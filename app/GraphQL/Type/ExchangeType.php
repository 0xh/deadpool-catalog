<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

class ExchangeType extends BaseType
{
    protected $attributes = [
        'name' => 'ExchangeType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The id of the exchange'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'The name of exchange'
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'The description of exchange'
            ],
            'is_active' => [
                'type' => Type::string(),
                'description' => 'The is_active of exchange'
            ],
            'countries' => [
                'type' => Type::listOf(GraphQL::type('CountryType')),
                'description' => 'The relation details'
            ],
            'exchange_logs' => [
                'type' => Type::listOf(GraphQL::type('ExchangeLogType')),
                'description' => 'The relation details'
            ],
        ];
    }
}
