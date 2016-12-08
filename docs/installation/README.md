# Installation

## Composer

Add the following to your composer.json file

```json
{  
    "require": {  
        "cyber-duck/silverstripe-block-page": "1.0.*"
    }
}
```

## Extension

In your config.yml file add the block page extension to a Page object

```yml
HomePage:
  extensions:
    - BlockPageExtension
```

Run composer and then visit /dev/build?flush=all to rebuild the database and flush the cache.