<?php

use Myoutdeskllc\SalesforcePhpQueryBuilder\Exceptions\InvalidQueryException;
use Myoutdeskllc\SalesforcePhpQueryBuilder\QueryBuilder;

it('builds base query', function () {
    $qb = (new QueryBuilder())
        ->from('Account')
        ->select(['Id', 'Name', 'Description'])
        ->where('Name', '=', 'Mikhail')
        ->orderBy('Name')
        ->limit(10)
        ->offset(15);

    expect($qb->toSoql())->toBe("SELECT Id, Name, Description FROM Account WHERE Name = 'Mikhail' ORDER BY Name ASC LIMIT 10 OFFSET 15");
});

it('handles boolean where', function () {
    $qb = (new QueryBuilder())
        ->from('Account')
        ->select(['Id', 'Name', 'Description'])
        ->where('IsChecked', '=', true);

    expect($qb->toSoql())->toBe("SELECT Id, Name, Description FROM Account WHERE IsChecked = true");
});

it('handles number where', function () {
    $qb = (new QueryBuilder())
        ->from('Account')
        ->select(['Id', 'Name', 'Description'])
        ->where('Amount', '=', 0);

    expect($qb->toSoql())->toBe("SELECT Id, Name, Description FROM Account WHERE Amount = 0");
});

it('supports several order clauses', function () {
    $qb = (new QueryBuilder())
        ->from('Account')
        ->select(['Id', 'Name', 'Description'])
        ->orderBy('Name')
        ->orderByDesc('Description');

    expect($qb->toSoql())->toBe('SELECT Id, Name, Description FROM Account ORDER BY Name ASC, Description DESC');
});

it('handles orWhere', function () {
    $qb = (new QueryBuilder())
        ->from('Account')
        ->select(['Id', 'Name', 'Description'])
        ->orWhere('A', '=', 'B')
        ->orWhere('C', '=', 'D');

    expect($qb->toSoql())->toBe("SELECT Id, Name, Description FROM Account WHERE A = 'B' OR C = 'D'");
});

it('handles whereIn', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->where('G', '=', 'G')
        ->whereIn('Name', ["A", "B", "C"]);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE G = 'G' AND Name IN ('A', 'B', 'C')");
});

it('handles whereNotIn', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->where('G', '=', 'G')
        ->whereNotIn('Name', ["A", "B", "C"]);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE G = 'G' AND Name NOT IN ('A', 'B', 'C')");
});

it('handles orWhereIn', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->where('G', '=', 'G')
        ->orWhereIn('Name', ["A", "B", "C"]);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE G = 'G' OR Name IN ('A', 'B', 'C')");
});

it('handles orWhereNotIn', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->where('G', '=', 'G')
        ->orWhereNotIn('Name', ["A", "B", "C"]);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE G = 'G' OR Name NOT IN ('A', 'B', 'C')");
});

it('throws exception when query has no fields', function () {
    expect(fn () => (new QueryBuilder())
        ->from('Account')
        ->orderBy('Name')
        ->orderByDesc('Description')
        ->toSoql()
    )->toThrow(InvalidQueryException::class);
});

it('throws exception when query has no SObject', function () {
    expect(fn () => (new QueryBuilder())
        ->select(['Id', 'Name', 'Description'])
        ->orderBy('Name')
        ->orderByDesc('Description')
        ->toSoql()
    )->toThrow(InvalidQueryException::class);
});

it('can add selection', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->addSelect('Description');

    expect($qb->toSoql())->toBe("SELECT Id, Name, Description FROM Acc");
});

it('supports whereColumn', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->whereColumn([['A', '>', 3], ['B', '<', 8]]);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE A > 3 AND B < 8");
});

it('handles where with null', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->where('A', '=', null);

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE A = null");
});

it('handles whereDate', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->whereDate("A", "=", "2019-10-10");

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE A = 2019-10-10");
});

it('handles orWhereDate', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->select(['Id', 'Name'])
        ->whereDate("A", "=", "2019-10-10")
        ->orWhereDate("B", "=", "2019-10-09");

    expect($qb->toSoql())->toBe("SELECT Id, Name FROM Acc WHERE A = 2019-10-10 OR B = 2019-10-09");
});

it('prevents duplicate selects', function () {
    $qb = (new QueryBuilder())
        ->from('Acc')
        ->addSelect("Id")
        ->addSelect("Id");

    expect($qb->toSoql())->toBe("SELECT Id FROM Acc");
});

it('supports whereFunction', function () {
    $qb = (new QueryBuilder())
        ->from('Object')
        ->addSelect('Id')
        ->whereFunction('F', 'func1', 'chs1')
        ->whereFunction('F', 'func2', 'chs2')
        ->whereFunction('F', 'func3', 'chs3', 'OR')
        ->whereFunction('F', 'func4', 'chs4');

    expect($qb->toSoql())->toBe("SELECT Id FROM Object WHERE F func1('chs1') AND F func2('chs2') OR F func3('chs3') AND F func4('chs4')");
});

it('supports whereFunction with array', function () {
    $qb = (new QueryBuilder())
        ->from('Object')
        ->addSelect('Id')
        ->whereFunction('F', 'func1', ['chs1;chs2', 'chs3', 'chs4']);

    expect($qb->toSoql())->toBe("SELECT Id FROM Object WHERE F func1('chs1;chs2', 'chs3', 'chs4')");
});

it('groups conditional expressions', function () {
    $qb = (new QueryBuilder())
        ->from('Androids__c')
        ->addSelect('Id')
        ->where('Warranty', '=', 'Expired')
        ->startWhere()
        ->orWhere('Warranty', '=', 'Active')
        ->where('Days_Left__c', '<=', '60')
        ->endWhere();

    expect($qb->toSoql())->toBe("SELECT Id FROM Androids__c WHERE Warranty = 'Expired' OR (Warranty = 'Active' AND Days_Left__c <= '60')");
});

it('groups conditional expressions alone', function () {
    $qb = (new QueryBuilder())
        ->from('Androids__c')
        ->addSelect('Id')
        ->where('Warranty', '=', 'Expired')
        ->startWhere()
        ->where('Days_Left__c', '<=', '60')
        ->endWhere();

    expect($qb->toSoql())->toBe("SELECT Id FROM Androids__c WHERE Warranty = 'Expired' AND (Days_Left__c <= '60')");
});

it('groups expressions in multiple locations', function () {
    $qb = (new QueryBuilder())
        ->from('Androids__c')
        ->addSelect('Id')
        ->startWhere()
        ->where('Warranty', '=', 'Active')
        ->where('Days_Left__c', '<=', '60')
        ->endWhere()
        ->startWhere()
        ->orWhere('Warranty', '=', 'Expired')
        ->where('Days_Expired__c', '<=', '30')
        ->endWhere();

    expect($qb->toSoql())->toBe("SELECT Id FROM Androids__c WHERE (Warranty = 'Active' AND Days_Left__c <= '60') OR (Warranty = 'Expired' AND Days_Expired__c <= '30')");
});

it('groups conditional expressions nested', function () {
    $qb = (new QueryBuilder())
        ->from('Androids__c')
        ->addSelect('Id')
        ->startWhere()
        ->startWhere()
        ->where('Warranty', '=', 'Active')
        ->where('Days_Left__c', '<=', '60')
        ->endWhere()
        ->startWhere()
        ->orWhere('Warranty', '=', 'Expired')
        ->where('Days_Expired__c', '<=', '30')
        ->endWhere()
        ->orWhere('Select_This_Anyway__c', '=', 'true')
        ->endWhere();

    expect($qb->toSoql())->toBe("SELECT Id FROM Androids__c WHERE ((Warranty = 'Active' AND Days_Left__c <= '60') OR (Warranty = 'Expired' AND Days_Expired__c <= '30') OR Select_This_Anyway__c = 'true')");
});

it('throws exception for mismatched grouping', function () {
    expect(fn () => (new QueryBuilder())
        ->from('Androids__c')
        ->addSelect('Id')
        ->startWhere()
        ->where('Warranty', '=', 'Active')
        ->toSoql()
    )->toThrow(InvalidQueryException::class);
});

it('throws exception for missing object', function () {
    expect(fn () => (new QueryBuilder())
        ->from('')
        ->addSelect('Id')
        ->toSoql()
    )->toThrow(InvalidQueryException::class);
});

it('throws exception for missing fields', function () {
    expect(fn () => (new QueryBuilder())
        ->from('Androids')
        ->toSoql()
    )->toThrow(InvalidQueryException::class);
});
