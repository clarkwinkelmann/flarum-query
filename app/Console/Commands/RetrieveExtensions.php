<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Composer\Semver\Comparator;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class RetrieveExtensions extends Command
{
    protected $signature = 'extensions:retrieve {--overwrite}';
    protected $description = 'Retrieve extensions via Packagist';

    protected $client;
    protected $yamlParser;
    protected $db;

    public function __construct(DatabaseManager $db)
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => 'https://packagist.org',
        ]);

        $this->yamlParser = new Parser();

        $this->db = $db->connection('query-write');
    }

    public function handle()
    {
        $this->db->table('extensions')->truncate();

        $url = '/search.json?type=flarum-extension';

        while ($url) {
            $this->info("Reading $url");

            $response = $this->client->get($url);
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $packages = Arr::get($data, 'results', []);

            foreach ($packages as $package) {
                $packageName = Arr::get($package, 'name');
                $this->info("Saving $packageName");

                $details = Arr::get(\GuzzleHttp\json_decode($this->client->get("/packages/$packageName.json")->getBody()->getContents(), true), 'package', []);

                $flarumid = str_replace([
                    'flarum-ext-',
                    'flarum-',
                    '/',
                ], [
                    '',
                    '',
                    '-',
                ], $packageName);

                $this->db->table('extensions')->insert([
                    'package' => $packageName,
                    'flarumid' => $flarumid,
                    'repository' => Arr::get($details, 'repository'),
                    'abandoned' => Arr::get($details, 'abandoned'),
                    'github_stars' => Arr::get($details, 'github_stars'),
                    'github_watchers' => Arr::get($details, 'github_watchers'),
                    'github_forks' => Arr::get($details, 'github_forks'),
                    'github_open_issues' => Arr::get($details, 'github_open_issues'),
                    'downloads_total' => Arr::get($details, 'downloads.total'),
                    'downloads_monthly' => Arr::get($details, 'downloads.monthly'),
                    'downloads_daily' => Arr::get($details, 'downloads.daily'),
                ]);

                $versions = Arr::get($details, 'versions', []);

                $lastVersion = null;

                foreach ($versions as $versionData) {
                    $versionNumber = Arr::get($versionData, 'version');
                    $this->info("Saving version $versionNumber");

                    if (
                        Str::startsWith($versionNumber, 'dev-') ||
                        Str::endsWith($versionNumber, '-dev')
                    ) {
                        continue;
                    }

                    $this->readPackagist($packageName, $versionNumber, $versionData, $this->option('overwrite'));

                    if (is_null($lastVersion) || Comparator::greaterThan($versionNumber, $lastVersion)) {
                        $lastVersion = $versionNumber;
                    }
                }

                if ($lastVersion) {
                    $this->readPackagist($packageName, 'latest', Arr::get($versions, $lastVersion), true);
                }
            }

            $url = Arr::get($data, 'next');
        }

        $this->info('Done.');
    }

    protected function readPackagist(string $package, string $version, array $data, $forceOverwrite = false)
    {
        $where = function (Builder $builder) use ($package, $version) {
            return $builder->where('package', $package)->where('version', $version);
        };

        if ($forceOverwrite) {
            $where($this->db->table('composer_requirements'))->delete();
            $where($this->db->table('issues'))->delete();
            $where($this->db->table('javascript_imports'))->delete();
            $where($this->db->table('javascript_initializers'))->delete();
            $where($this->db->table('language_packs'))->delete();
            $where($this->db->table('php_imports'))->delete();
            $where($this->db->table('releases'))->delete();
            $where($this->db->table('route_definitions'))->delete();
            $where($this->db->table('translation_definitions'))->delete();
            $where($this->db->table('translation_usages'))->delete();
        } else if ($where($this->db->table('releases'))->exists()) {
            // By default, skip if the data already exists
            return;
        }

        $this->db->table('releases')->insert([
            'package' => $package,
            'version' => $version,
            'license' => Arr::get($data, 'license') ? implode(',', Arr::get($data, 'license')) : null,
            'title' => Arr::get($data, 'extra.flarum-extension.title'),
            'description' => Arr::get($data, 'description'),
            'icon_name' => Arr::get($data, 'extra.flarum-extension.icon.name'),
            'icon_image' => Arr::get($data, 'extra.flarum-extension.icon.image'),
            'icon_color' => Arr::get($data, 'extra.flarum-extension.icon.color'),
            'icon_background' => Arr::get($data, 'extra.flarum-extension.icon.backgroundColor'),
            'discuss' => Arr::get($data, 'extra.flagrow.discuss'),
            'date' => Carbon::parse(Arr::get($data, 'time')),
        ]);

        if ($locale = Arr::get($data, 'extra.flarum-locale.code')) {
            $this->db->table('language_packs')->insert([
                'package' => $package,
                'version' => $version,
                'locale' => $locale,
                'title' => Arr::get($data, 'extra.flarum-locale.title'),
            ]);
        }

        $require = Arr::get($data, 'require', []);
        if (count($require)) {
            $this->db->table('composer_requirements')->insert(array_map(function ($constraint, $otherPackage) use ($package, $version) {
                return [
                    'package' => $package,
                    'version' => $version,
                    'other_package' => $otherPackage,
                    'constraint' => $constraint,
                ];
            }, $require, array_keys($require)));
        }

        $distUrl = Arr::get($data, 'dist.url');

        if (!$distUrl) {
            $this->db->table('issues')->insert([
                'package' => $package,
                'version' => $version,
                'file' => 'dist',
                'description' => "Missing dist url",
            ]);

            return;
        }

        if (preg_match('~^https://api\.github\.com/repos/[^/]+/[^/]+/zipball/[0-9a-f]+$~', $distUrl) !== 1) {
            $this->db->table('issues')->insert([
                'package' => $package,
                'version' => $version,
                'file' => 'dist',
                'description' => "Dist file $distUrl not supported at this time",
            ]);

            return;
        }

        try {
            $zipContent = cache()->rememberForever($distUrl, function () use ($distUrl) {
                $response = $this->client->get($distUrl);

                return $response->getBody()->getContents();
            });
        } catch (\Exception $exception) {
            $this->db->table('issues')->insert([
                'package' => $package,
                'version' => $version,
                'file' => 'dist',
                'description' => "Error fetching $distUrl: " . $exception->getMessage(),
            ]);

            return;
        }

        $tmp = tempnam('/tmp', 'flarumbigquery');

        file_put_contents($tmp, $zipContent);

        $zip = new \ZipArchive();
        $zip->open($tmp);

        $rootDirectory = $zip->numFiles ? $zip->getNameIndex(0) : '';

        for ($fileIndex = 0; $fileIndex < $zip->numFiles; $fileIndex++) {
            $filename = $zip->getNameIndex($fileIndex);

            // Remove the root directory from the file path
            if (strpos($filename, $rootDirectory) === 0) {
                $filename = substr($filename, strlen($rootDirectory));
            }

            if (preg_match('~dist/(admin|forum)\.js$~', $filename, $matches) === 1) {
                $this->readJavascriptFile($package, $version, $filename, $zip->getFromIndex($fileIndex));
            }

            if (preg_match('~locales?/([^/]+)\.ya?ml$~', $filename, $matches) === 1) {
                $this->readTranslationFile($package, $version, $filename, $locale ?? $matches[1], $zip->getFromIndex($fileIndex));
            }

            if (preg_match('~\.php$~', $filename, $matches) === 1) {
                $this->readPhpFile($package, $version, $filename, $zip->getFromIndex($fileIndex));
            }
        }

        unlink($tmp);
    }

    protected function readJavascriptFile(string $package, string $version, string $file, string $content)
    {
        preg_match_all('~\.initializers\.add\("([^"]+)"~', $content, $translationMatches);
        $this->db->table('javascript_initializers')->insert(array_map(function ($key) use ($package, $version, $file) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'key' => $key,
            ];
        }, $translationMatches[1]));

        preg_match_all('~\.translator\.trans\("([^"]+)"~', $content, $translationMatches);
        $this->db->table('translation_usages')->insert(array_map(function ($key) use ($package, $version, $file) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'key' => $key,
                // TODO: parameters
            ];
        }, $translationMatches[1]));

        preg_match_all('~flarum\.core\.compat\["([^"]+)"\]~', $content, $importMatchesBrackets);
        preg_match_all('~flarum\.core\.compat\.([A-Za-z]+)~', $content, $importMatchesDot);
        $this->db->table('javascript_imports')->insert(array_map(function ($class) use ($package, $version, $file) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'class' => 'flarum/' . $class,
            ];
        }, array_merge($importMatchesBrackets[1], $importMatchesDot[1])));
    }

    protected function readTranslationFile(string $package, string $version, string $file, string $locale, string $content)
    {
        // Based on Symfony\Component\Translation\Loader\YamlFileLoader
        try {
            $messages = $this->yamlParser->parse($content);
        } catch (ParseException $exception) {
            $this->db->table('issues')->insert([
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'description' => 'ParseException: ' . $exception->getMessage(),
            ]);

            return;
        }

        // empty resource
        if ($messages === null) {
            $messages = [];
        }

        // not an array
        if (!is_array($messages)) {
            $this->db->table('issues')->insert([
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'description' => 'Root is not an array',
            ]);

            return;
        }

        $strings = Arr::dot($messages);

        $this->db->table('translation_definitions')->insert(array_map(function ($value, $key) use ($package, $version, $file, $locale) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'locale' => $locale,
                'key' => $key,
                'value' => $value,
            ];
        }, $strings, array_keys($strings)));
    }

    protected function readPhpFile(string $package, string $version, string $file, string $content)
    {
        preg_match_all('~\->trans\(\'([^\']+)\'~', $content, $importMatchesSingleQuote);
        preg_match_all('~\->trans\("([^"]+)"~', $content, $importMatchesDoubleQuote);
        $this->db->table('translation_usages')->insert(array_map(function ($key) use ($package, $version, $file) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'key' => $key,
                // TODO: parameters
            ];
        }, array_merge($importMatchesSingleQuote[1], $importMatchesDoubleQuote[1])));

        preg_match_all('~^use\s+([^ ]+)\s*(?:as\s.*)?;\s*$~m', $content, $importMatches);
        // TODO: calls without imports
        // TODO: calls relative to imports
        $this->db->table('php_imports')->insert(array_map(function ($class) use ($package, $version, $file) {
            return [
                'package' => $package,
                'version' => $version,
                'file' => $file,
                'class' => $class,
            ];
        }, $importMatches[1]));

        preg_match_all('~\(new\s+Extend\\\Routes\([\'"](api|forum|admin)[\'"]\)\)~', $content, $routeExtenders, PREG_OFFSET_CAPTURE);
        foreach ($routeExtenders[0] as $i => $routeExtender) {
            $stack = $routeExtenders[1][$i][0];

            $stringAfterLastMatch = substr($content, $routeExtender[1] + strlen($routeExtender[0]));

            $routes = [];

            while (true) {
                // TODO: ->route(method, ...)
                // Test single quotes first, then double quotes
                if (preg_match('~^\s*\->(get|post|put|patch|delete)\(\s*\'([^\']+)\'\s*,\s*\'[^\']+\'\s*,[^)]+\)~', $stringAfterLastMatch, $routeAddMatch, PREG_OFFSET_CAPTURE) !== 1) {
                    if (preg_match('~^\s*\->(get|post|put|patch|delete)\(\s*"([^"]+)"\s*,\s*"[^"]+"\s*,[^)]+\)~', $stringAfterLastMatch, $routeAddMatch, PREG_OFFSET_CAPTURE) !== 1) {
                        break;
                    }
                }

                $routes[] = [
                    'package' => $package,
                    'version' => $version,
                    'file' => $file,
                    'method' => strtoupper($routeAddMatch[1][0]),
                    'path' => ($stack === 'forum' ? '' : '/' . $stack) . $routeAddMatch[2][0],
                ];

                $stringAfterLastMatch = substr($content, $routeAddMatch[0][1] + strlen($routeAddMatch[0][0]));
            }

            if (count($routes)) {
                $this->db->table('route_definitions')->insert($routes);
            }
        }
    }
}
