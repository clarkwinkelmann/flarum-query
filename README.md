# Flarum "big" query

An attempt at bringing big data to Flarum.
This tool let us analyze all extensions via SQL queries.

Try it out at <https://query.flarum.dev/>

Some example queries:

### Routes defined by multiple extensions

```sql
select method, path, count(1) as count from route_definitions
where version = 'latest'
group by method, path
having count > 1
order by count desc
```

### Packages defining translations that don't start with their extension ID

```sql
select d.package, count(1) as string_not_matching_count from translation_definitions d
join extensions e on e.package = d.package
where version = 'latest' and `key` not like concat(flarumid, '.%')
group by d.package
order by string_not_matching_count desc
```

### Detailed list of translations used by packages that don't start with their extension ID

```sql
select d.package, flarumid, `key` from translation_usages d
join extensions e on e.package = d.package
where version = 'latest' and `key` not like concat(flarumid, '.%')
order by d.package
```

### How many extensions use each license

```sql
select license, count(1) as count from releases
where version = 'latest'
group by license
order by count desc
```

### All packages that don't use the MIT license

```sql
select package, license, title, description from releases
where license != 'MIT' and version = 'latest'
order by package
```

### Most imported PHP classes from the Flarum namespace

```sql
select class, count(1) as count from php_imports
where version = 'latest' and class like 'Flarum%' and package not like 'flarum/%'
group by class
order by count desc
```

### Most imported PHP classes in packages released recently

```sql
select class, count(distinct i.package) as extensions_count, count(1) as import_count from php_imports i
join releases r on r.package = i.package and r.version = i.version
where i.version = 'latest' and class like 'Flarum%' and i.package not like 'flarum/%' and date >= '2020-01-01'
group by class
order by extensions_count desc
```
