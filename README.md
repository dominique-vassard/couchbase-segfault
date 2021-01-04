# Segmentation fault in couchbase while using FTS

This project demonstrates how to cause segmentation fault when using fts with couchbase 6.6 (php sdk 3.0.5)

### Prerequisite
You'll need:
    - a working couchbase cluster (6.6) with the following:  
        - username: `couchbase`  
        - password: `couch_test`  
        - bucket: `test`   
        - index storage mode: `Memory-Optimized` (even though I don't know if this has an impact of segmentation fault)  
    - a fts index defined by the following json:  
```
{
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
}
```

### Build your image
`docker build -t couchbase-segfault .`  

### Run the script
`docker run --rm -ti --name php-script -v "$PWD":/usr/src/app -w /usr/src/app  couchbase-segfault php segfault.php`  
Now, check your working dir, the core has been dumped.  


In the script, you'll see this line:  
```
//This second call will cause the segmentation fault
    $res = search_by_email($cluster, 'john.duff@mymail.com');
```
You can comment it if you want to see the script exit with a successful result.  