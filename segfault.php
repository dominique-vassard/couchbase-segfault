<?php

test_seg_fault();

function test_seg_fault()
{
    // Connection
    $config = config();
    list($cluster, $collection) = connect_couchbase($config);

    // add_fixtures($collection);
    $res = search_by_email($cluster, 'john.duff@mymail.com');

    //This second call will cause the segmentation fault
    $res = search_by_email($cluster, 'john.duff@mymail.com');

    var_dump($res);

    // Clean data
    flush_bucket($cluster, $config['bucket']);
}

function config()
{
    return [
        'host' => '192.168.56.101',
        'username' => 'couchbase',
        'password' => 'couch_test',
        'bucket' => 'test'
    ];
}

function connect_couchbase($config)
{

    $connectionString = $config['host'];
    $options = new \Couchbase\ClusterOptions();
    $options->credentials($config['username'], $config['password']);
    $cluster = new \Couchbase\Cluster($connectionString, $options);
    // get a bucket reference
    $bucket = $cluster->bucket($config['bucket']);
    $collection = $bucket->defaultCollection();

    return [$cluster, $collection];
}

function flush_bucket($cluster, $bucket)
{
    echo "Flushing...\n";
    $bucketManager = $cluster->buckets();
    $bucketManager->flush($bucket);
}

function add_fixtures($collection)
{
    $users = [
        [
            'type' => 'user',
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Duff',
            'email' => 'john.duff@mymail.com'
        ],
        [
            'type' => 'user',
            'id' => 2,
            'first_name' => 'EJohn',
            'last_name' => 'Other Duff',
            'email' => 'ejohn.duff@mymail.com'
        ]
    ];

    foreach ($users as $user) {
        $collection->insert('user::' . $user['id'], $user);
    }
}

function search_by_email($cluster, $email)
{
    $search_query = new \Couchbase\MatchSearchQuery($email);
    $search_query->field('email');

    $options = new \Couchbase\SearchOptions();
    $options->fields(['email']);
    $options->limit(1);
    $search_result = $cluster->searchQuery('user_unique_email', $search_query, $options);

    return $search_result->rows();
}

function index_definition()
{
    $json = '{
        "type": "fulltext-index",
        "name": "user_unique_email",
        "uuid": "1ee1765591743ab4",
        "sourceType": "couchbase",
        "sourceName": "test",
        "sourceUUID": "3a827762b7a0da7c06261a5541ce1910",
        "planParams": {
          "maxPartitionsPerPIndex": 171,
          "indexPartitions": 6
        },
        "params": {
          "doc_config": {
            "docid_prefix_delim": "",
            "docid_regexp": "",
            "mode": "type_field",
            "type_field": "type"
          },
          "mapping": {
            "analysis": {
              "analyzers": {
                "unique_email": {
                  "char_filters": [
                    "asciifolding"
                  ],
                  "token_filters": [
                    "to_lower",
                    "unique"
                  ],
                  "tokenizer": "single",
                  "type": "custom"
                }
              }
            },
            "default_analyzer": "standard",
            "default_datetime_parser": "dateTimeOptional",
            "default_field": "_all",
            "default_mapping": {
              "dynamic": true,
              "enabled": true
            },
            "default_type": "_default",
            "docvalues_dynamic": true,
            "index_dynamic": true,
            "store_dynamic": false,
            "type_field": "_type",
            "types": {
              "user": {
                "dynamic": false,
                "enabled": true,
                "properties": {
                  "email": {
                    "dynamic": false,
                    "enabled": true,
                    "fields": [
                      {
                        "analyzer": "unique_email",
                        "docvalues": true,
                        "include_in_all": true,
                        "include_term_vectors": true,
                        "index": true,
                        "name": "email",
                        "type": "text"
                      }
                    ]
                  }
                }
              }
            }
          },
          "store": {
            "indexType": "scorch"
          }
        },
        "sourceParams": {}
      }';
    return $json;
}
