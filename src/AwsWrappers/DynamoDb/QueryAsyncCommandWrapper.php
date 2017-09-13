<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2017-01-06
 * Time: 11:40
 */

namespace Oasis\Mlib\AwsWrappers\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use GuzzleHttp\Promise\PromiseInterface;
use Oasis\Mlib\AwsWrappers\DynamoDbIndex;
use Oasis\Mlib\AwsWrappers\DynamoDbItem;

class QueryAsyncCommandWrapper
{
    /**
     * @param DynamoDbClient $dbClient
     * @param                $tableName
     * @param                $keyConditions
     * @param array          $fieldsMapping
     * @param array          $paramsMapping
     * @param                $indexName
     * @param                $filterExpression
     * @param                $lastKey
     * @param                $evaluationLimit
     * @param                $isConsistentRead
     * @param                $isAscendingOrder
     * @param                $countOnly
     *
     * @return PromiseInterface
     */
    function __invoke(DynamoDbClient $dbClient,
                      $tableName,
                      $keyConditions,
                      array $fieldsMapping,
                      array $paramsMapping,
                      $indexName,
                      $filterExpression,
                      &$lastKey,
                      $evaluationLimit,
                      $isConsistentRead,
                      $isAscendingOrder,
                      $countOnly)
    {
        $requestArgs = [
            "TableName"        => $tableName,
            "ConsistentRead"   => $isConsistentRead,
            "ScanIndexForward" => $isAscendingOrder,
        ];
        if ($countOnly) {
            $requestArgs['Select'] = "COUNT";
        }
        if ($keyConditions) {
            $requestArgs['KeyConditionExpression'] = $keyConditions;
        }
        if ($filterExpression) {
            $requestArgs['FilterExpression'] = $filterExpression;
        }
        if ($keyConditions || $filterExpression) {
            if ($fieldsMapping) {
                $requestArgs['ExpressionAttributeNames'] = $fieldsMapping;
            }
            if ($paramsMapping) {
                $paramsItem                               = DynamoDbItem::createFromArray($paramsMapping);
                $requestArgs['ExpressionAttributeValues'] = $paramsItem->getData();
            }
        }
        if ($indexName !== DynamoDbIndex::PRIMARY_INDEX) {
            $requestArgs['IndexName'] = $indexName;
        }
        if ($lastKey) {
            $requestArgs['ExclusiveStartKey'] = $lastKey;
        }
        if ($evaluationLimit) {
            $requestArgs['Limit'] = $evaluationLimit;
        }
        $promise = $dbClient->queryAsync($requestArgs);
        
        return $promise;
    }
}
