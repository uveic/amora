<?php

namespace Amora\Core\Module;

use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Util\StringUtil;

trait DataLayerTrait {
    public function generateWhereSqlCodeForIds(
        array &$params,
        array $ids,
        string $dbColumnName,
        ?string $keyName = null,
        bool $notIn = false,
    ): string {
        $keyName = $keyName ?? (StringUtil::generateRandomString(6) . 'Id');

        $allKeys = [];
        foreach (array_values($ids) as $key => $addressId) {
            $currentKey = ':' . $keyName . $key;
            $allKeys[] = $currentKey;
            $params[$currentKey] = $addressId;
        }

        return ' AND ' . $dbColumnName . ($notIn ? ' NOT' : '') . ' IN (' . implode(', ', $allKeys) . ')';
    }

    public function generateOrderByAndLimitCode(
        QueryOptions $queryOptions,
        array $orderByMapping,
    ): string {
        $orderBy = $this->generateOrderByCode($queryOptions, $orderByMapping);
        $limit = $this->generateLimitCode($queryOptions);

        return $orderBy . $limit;
    }

    private function generateLimitCode(QueryOptions $queryOptions): string
    {
        return ' LIMIT ' . $queryOptions->getItemsPerPage() . ' OFFSET ' . $queryOptions->getOffset();
    }

    private function generateOrderByCode(QueryOptions $queryOptions, array $orderByMapping): string
    {
        if ($queryOptions->orderRandomly) {
            return ' ORDER BY RAND()';
        }

        if ($orderByMapping) {
            $orderByParts = [];
            /** @var QueryOrderBy $item */
            foreach ($queryOptions->orderBy as $item) {
                if (empty($orderByMapping[$item->field])) {
                    continue;
                }

                $orderByParts[] = $orderByMapping[$item->field] . ' ' . $item->direction->value;
            }

            if (empty($orderByParts)) {
                return '  ORDER BY  ' . array_values($orderByMapping)[0] . ' ASC';
            }

            return ' ORDER BY ' . implode(', ', $orderByParts);
        }

        return '';
    }
}
