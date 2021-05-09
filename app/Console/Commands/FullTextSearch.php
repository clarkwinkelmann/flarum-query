<?php

namespace App\Console\Commands;

use Composer\Semver\Comparator;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class FullTextSearch extends Command
{
    protected $signature = 'extensions:fulltextsearch {text} {--extension=} {--only-cached}';
    protected $description = 'Perform a full text search in all extensions source code';

    protected $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => 'https://packagist.org',
        ]);
    }

    public function handle()
    {
        $url = '/search.json?type=flarum-extension';

        while ($url) {
            $this->line("Reading $url", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $response = $this->client->get($url);
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $packages = Arr::get($data, 'results', []);

            foreach ($packages as $package) {
                $packageName = Arr::get($package, 'name');

                $details = Arr::get(\GuzzleHttp\json_decode($this->client->get("/packages/$packageName.json")->getBody()->getContents(), true), 'package', []);

                $versions = Arr::get($details, 'versions', []);

                $lastVersion = null;

                foreach ($versions as $versionData) {
                    $versionNumber = Arr::get($versionData, 'version');

                    if (
                        Str::startsWith($versionNumber, 'dev-') ||
                        Str::endsWith($versionNumber, '-dev')
                    ) {
                        continue;
                    }

                    if (is_null($lastVersion) || Comparator::greaterThan($versionNumber, $lastVersion)) {
                        $lastVersion = $versionNumber;
                    }
                }

                if ($lastVersion) {
                    $this->line("Reading $packageName version $lastVersion", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    $latestVersionData = Arr::get($versions, $lastVersion);

                    if (!is_array($latestVersionData)) {
                        $this->warn("Package $packageName version $lastVersion is invalid", OutputInterface::VERBOSITY_VERBOSE);
                    } else {
                        $this->readPackagist($packageName, $lastVersion, $latestVersionData);
                    }
                } else {
                    $this->warn("No latest version for $packageName", OutputInterface::VERBOSITY_VERBOSE);
                }
            }

            $url = Arr::get($data, 'next');
        }

        $this->line('Done.');
    }

    protected function readPackagist(string $package, string $version, array $data)
    {
        $distUrl = Arr::get($data, 'dist.url');

        if ($this->option('only-cached') && !cache()->has($distUrl)) {
            $this->warn("File not cached $distUrl", OutputInterface::VERBOSITY_VERBOSE);

            return;
        }

        try {

            $zipContent = cache()->rememberForever($distUrl, function () use ($distUrl) {
                $response = $this->client->get($distUrl);

                return $response->getBody()->getContents();
            });
        } catch (\Exception $exception) {
            $this->warn("Error fetching $distUrl: " . $exception->getMessage());

            return;
        }

        $tmp = tempnam('/tmp', 'flarumbigquery');

        file_put_contents($tmp, $zipContent);

        $zip = new \ZipArchive();
        $zip->open($tmp);

        $rootDirectory = $zip->numFiles ? $zip->getNameIndex(0) : '';

        $fileExtensions = explode(',', $this->option('extension') ?? 'js,php');

        for ($fileIndex = 0; $fileIndex < $zip->numFiles; $fileIndex++) {
            $filename = $zip->getNameIndex($fileIndex);

            // Remove the root directory from the file path
            if (strpos($filename, $rootDirectory) === 0) {
                $filename = substr($filename, strlen($rootDirectory));
            }

            if (!in_array(pathinfo($filename, PATHINFO_EXTENSION), $fileExtensions)) {
                continue;
            }

            $content = $zip->getFromIndex($fileIndex);

            foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $index => $line) {
                if (str_contains($line, $this->argument('text'))) {
                    $this->info("Found in $package@$version $filename:" . ($index + 1));
                    $this->info($line);
                }
            }
        }

        unlink($tmp);
    }
}
