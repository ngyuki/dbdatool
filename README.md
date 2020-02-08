# dbdatool

[![Build Status](https://travis-ci.org/ngyuki/dbdatool.svg?branch=master)](https://travis-ci.org/ngyuki/dbdatool)
[![Latest Stable Version](https://poser.pugx.org/ngyuki/dbdatool/v/stable)](https://packagist.org/packages/ngyuki/dbdatool)
[![Latest Unstable Version](https://poser.pugx.org/ngyuki/dbdatool/v/unstable)](https://packagist.org/packages/ngyuki/dbdatool)
[![License](https://poser.pugx.org/ngyuki/dbdatool/license)](https://packagist.org/packages/ngyuki/dbdatool)

database diff/apply tool.

稼働集のデータベースからスキーマ定義ファイルをダンプし、その定義と別のデータベースを比較して差分を `ALTER TABLE` などの SQL の形で表示したり、差分をデータベースへ適用したりするマイグレーションツールです。

`dbdatool dump > schema.json` でデータベースからスキーマ定義ファイルを出力します。`dbdatool apply schema.json` で `schema.json` に書かれたスキーマ定義とデータベースを比較して差分を `ALTER TABLE` などで適用します。

## Demo

- https://asciinema.org/a/rvRpRAZPtBBxNQkyqliYD69j4

## Install

You can download phar file, see https://github.com/ngyuki/dbdatool/releases

## 簡単な使い方

`config.php` にデータベース接続情報を記述します。

```php
<?php
return [
    'dsn' => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8',
    'username' => 'oreore',
    'password' => 'himitu',
];
```

`composer.json` にコンフィグのパスを追記します。

```json
{
    "extra": {
        "dbdatool-config": ["config.php"]
    }
}
```

データベースからスキーマ定義ファイルを出力します。

```sh
php dbdatool.phar dump > schema.json
```

スキーマ定義ファイルとデータベースを比較して差分を表示します。

```sh
php dbdatool.phar diff schema.json
```

差分をデータベースに適用します。

```sh
php dbdatool.phar apply schema.json
```

## dbdatool dump

動いているデータベースからスキーマ定義ファイルをダンプします。

```
Usage:
  dump [options] [--] [<source>]

Arguments:
  source                               Connection information or schema file [default: "@"]

Options:
  -c, --config[=CONFIG]                Config filename.
      --ignore-tables[=IGNORE-TABLES]  Ignore table regex patterns. (multiple values allowed)
  -o, --output=OUTPUT                  Output filename
  ...snip...
```

source にはダンプ元のデータソースを指定します。省略すれば `@` で、これはコンフィグで指定されたデータベースです。

## dbdatool diff

2 つのデータソースを比較して、差分を `ALTER TABLE` などの SQL で表示します。

```
Usage:
  diff [options] [--] <source> [<target>]

Arguments:
  source                               Connection information or schema file
  target                               Connection information or schema file [default: "@"]

Options:
  -c, --config[=CONFIG]                Config filename.
      --ignore-tables[=IGNORE-TABLES]  Ignore table regex patterns. (multiple values allowed)
  ...snip...
```

target を source に一致させるための差分が表示されます。例えば target のみにテーブルが存在すれば `DROP TABLE` されます（直感の逆になっているかも）。

target は省略可能です。省略すれば `@` で、これはコンフィグで指定されたデータベースです。つまりデータソースをひとつだけ指定した場合は「コンフィグのデータベースを、指定したデータソースのスキーマ定義に一致させるための DDL」が表示されます。

## dbdatool apply

2 つのデータソースを比較して、差分を実際にデータベースへ適用します。

```
Usage:
  apply [options] [--] <source> [<target>]

Arguments:
  source                               Connection information or schema file for source
  target                               Connection information for target database [default: "@"]

Options:
  -c, --config[=CONFIG]                Config filename.
      --ignore-tables[=IGNORE-TABLES]  Ignore table regex patterns. (multiple values allowed)
  ...snip...
```

source の定義を target に差分で反映させます。

target は省略可能です。省略すれば `@` で、これはコンフィグで指定されたデータベースです。つまりデータソースをひとつだけ指定した場合は「指定したデータソースのスキーマ定義を、コンフィグのデータベース反映」します。

## コンフィグファイルの指定

コンフィグファイルは `-c|--config` オプションで指定するか、オプションで指定しない場合は変わりに `composer.json` で次のように指定できます。

```json
{
    "extra": {
        "dbdatool-config": [
            "dbdatool.php",
            "dbdatool.php.dist"
        ]
    }
}
```

この場合 `dbdatool.php` があればそれを、なければ `dbdmtool.php.dist` が使用されます。

## データソースの指定

コマンドで指定するデータソース（データベースやスキーマ定義ファイル）は、下記のいずれかの形式で指定できます。

```sh
# コンフィグファイルで指定されたデータベース接続
@

# 他のファイルで定義されたデータベース接続
staging.php

# 空のデータソース
!

# DSN（ユーザー名とパスワードはコロン(:)で区切って DSN の後に指定）
mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password

# スキーマ定義ファイルのファイル名
schema.json
```

例えば次のように指定します。

```sh
# スキーマ定義ファイルの内容を、DSN 指定されたデータベースに反映
php dbdatool.phar apply schema.json mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password

# 空のデータソースを、コンフィグのデータベースに反映
# （すべてのテーブルが削除される）
php dbdatool.phar apply '!' @

# 別のコンフィグファイルのデータベースのスキーマ定義を、コンフィグのデータベースに反映
php dbdatool.phar apply staging.php @
```

一部のコマンドでは指定できるデータソースに制限があります。例えば `apply` コマンドの2番目のデータソース(`target`)には実際のデータベース接続を伴うデータソースを指定する必要があるため、スキーマ定義ファイルや空のデータソースを指定することはできません。

## よくある使い方

データベースのスキーマは生の SQL の `CREATE TABLE` などの DDL で管理しており `init.sql` にテーブル・インデックス・外部キー制約を作るための SQL が保存されています。

`config.php` は次のように環境変数を元にデータベース接続情報を返します。

```php
<?php
$host = getenv('MYSQL_HOST');
$port = getenv('MYSQL_PORT');
$dbname = getenv('MYSQL_DATABASE');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

return [
    'dsn' => "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
    'username' => $username,
    'password' => $password,
];
```

`composer.json` でコンフィグのファイルパスを指定します。

```json
{
    "extra": {
        "dbdatool-config": ["config.php"]
    }
}
```

開発中にスキーマ定義に変更があるときは最初に `init.sql`  を修正します。

```sh
vim init.sql
```

`init.sql` を適当なデータベースにインポートします。

```sh
mysql test -v < init.sql
```

スキーマ定義ファイルをダンプします。

```sh
MYSQL_DATABASE=test php dbdatool.phar dump > schema.json
```

実際のデータベースとの差分を確認して適用します。

```sh
php dbdatool.phar diff schema.json
php dbdatool.phar apply schema.json
```

Git リポジトリに追加・コミット・プッシュします。

```sh
git add init.sql schema.json
git commit -m 'Fix database schema'
git push
```

他の開発者は git pull の後にスキーマ定義の変更を適用できます。

```sh
git pull -r
php dbdatool.phar diff schema.json
php dbdatool.phar apply schema.json
```

## 制限

スキーマ定義の比較は非常に雑に行っています。

例えば MySQL では `boolean` は `tinyint` のエイリアスですが、スキーマ定義に `boolean` と記述すると実際のデータベースとの比較で `tinyint` とは異なるため差分が検出されます。`boolean` の差分を適用したとしても実際のデータベースでは `tinyint` なので、スキーマ定義に `boolean` が書かれていると何度 `apply` しても差分が出続けます。

このような自体を避けるためにスキーマ定義ファイルは手書きせず `dump` で稼働中のデータベースから出力することをオススメします。

## 類似のツール

- https://github.com/winebarrel/ridgepole
- https://github.com/schemalex/schemalex
- https://github.com/k0kubun/sqldef
- https://github.com/arima-ryunosuke/db-migration
