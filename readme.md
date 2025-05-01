# Salesforce PHP Query Builder
Helps assemble SOQL queries for use with Salesforce REST API.

## Features
- Fluent, chainable API
- Supports `WHERE`, `ORDER BY`, `LIMIT`, `OFFSET`
- Grouped and nested conditional expressions
- Support for `WHERE IN`, `WHERE NOT IN`, `OR WHERE IN`, `OR WHERE NOT IN`
- `WHERE NULL` and `WHERE DATE` clauses
- Function-based WHERE clauses
- 
## Installation
```
composer require myoutdeskllc/salesforce-php-query-builder
```

## Usage
```php
use Myoutdeskllc\SalesforcePhpQueryBuilder\QueryBuilder;

$qb = (new QueryBuilder())
    ->from('Account')
    ->select(['Id', 'Name', 'Description'])
    ->where('Name', '=', 'Mikhail')
    ->orderBy('Name')
    ->limit(10)
    ->offset(15);

$soql = $qb->toSoql();

echo $soql;
// SELECT Id, Name, Description FROM Account WHERE Name = 'Mikhail' ORDER BY Name ASC LIMIT 10 OFFSET 15

```

## Supported Clauses

### Where
```php
$qb->where('Amount', '=', 100);
$qb->whereNull('ClosedDate');
$qb->whereDate('CreatedDate', '=', '2024-01-01');
$qb->whereIn('Status', ['Active', 'Pending']);
$qb->whereNotIn('Stage', ['Closed Won', 'Closed Lost']);
$qb->orWhere('IsActive', '=', true);
```

### Grouped Conditions
```php
$qb->startWhere()
   ->where('A', '=', 1)
   ->orWhere('B', '=', 2)
   ->endWhere();
```

### Order, Limit, Offset
```php
$qb->orderBy('Name');
$qb->orderByDesc('CreatedDate');
$qb->limit(5);
$qb->offset(10);
```

## Tests
```
composer install
vendor/bin/pest
```