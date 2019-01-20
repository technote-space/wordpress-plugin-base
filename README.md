# Wordpress plugin base

Wordpress plugin 開発用のライブラリです。  
管理画面やAPIなどの追加や設定値の読み書き等を容易にする機能が用意されています。  

# 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

# 手順

## プラグインフォルダの作成

wp-content/plugins フォルダに プラグイン用のフォルダを作成

## プラグインファイルの作成

作成したプラグインフォルダに「プラグイン名.php」(例：example.php) を作成  
[標準プラグイン情報](https://wpdocs.osdn.jp/%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E3%81%AE%E4%BD%9C%E6%88%90#.E6.A8.99.E6.BA.96.E3.83.97.E3.83.A9.E3.82.B0.E3.82.A4.E3.83.B3.E6.83.85.E5.A0.B1)  
を参考にプラグインの情報を入力

## このライブラリのインストール

composer を使用してインストールします。  
作成したプラグインフォルダで以下のコマンドを実行します。  

```composer require technote/wordpress-plugin-base```

　  
複数のプラグインでこのライブラリを使用する場合、最新のものが自動的に使用されます。

## このライブラリの使用

作成したプラグインファイルにライブラリを使用する記述を追記します。  
プラグインファイルはおおよそ以下のようなものになります。

```
<?php
/*
Plugin Name: example
Plugin URI:
Description: Plugin Description
Author: example
Version: 0.0.0
Author URI: http://example.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Technote::get_instance( 'Example', __FILE__ );
```

このプラグインファイルと同じフォルダに「functions.php」を作成すると、いろいろな準備ができた後に自動的に読み込まれます。  
プラグインの構成は以下のようなものになります。

```
example
    |
    - example.php
    |
    - functions.php
    |
    - src
    |  |
        - classes
    |  |     |
    |  |     - controllers
    |  |     |      |
    |  |     |      - admin
    |  |     |      |
    |  |     |      - api 
    |  |     |
    |  |     - models
    |  |     |
    |  |     - tests
    |  |
    |  - views 
    |      |
    |      - admin
    |          |
    |          - help
    |
    - configs
```


## 基本設定
- configs/config.php  

|設定値|説明|
|---|---|
|main_menu_title|管理画面のメニュー名になります|
|db_version|DBの設定を変更したら更新します|
|twitter|ツイッターのアカウントを指定します（ダッシュボードでヘルプに表示されます。空で未使用）|
|github|Githubのアカウントを指定します（ダッシュボードでヘルプに表示されます。空で未使用）|
|contact_url|プラグインのお問い合わせ用のページのURLを指定します（ダッシュボードでヘルプに表示されます）|
|menu_image|管理画面のメニューアイコンを指定します|
|update_info_file_url|開発バージョンチェック情報用のURLを指定します|

- configs/db.php

設定例：
```
// テーブル名 => 設定
'test' => array(
    
    // primary key 設定
    'id'      => 'test_id',     // optional [default = $table_name . '_id']
    
    // カラム 設定
    'columns' => array(
    
        // 論理名 => 設定
        'name'   => array(
            'name'     => 'name_test',     // optional (物理名)
            'type'     => 'VARCHAR(32)',   // required
            'unsigned' => false,          // optional [default = false]
            'null'     => true,           // optional [default = true]
            'default'  => null,           // optional [default = null]
            'comment'  => '',             // optional
        ),
        'value1' => array(
            'type'    => 'VARCHAR(32)',
            'null'    => false,
            'default' => 'test',
        ),
        'value2' => array(
            'type'    => 'VARCHAR(32)',
            'comment' => 'aaaa',
        ),
        'value3' => array(
            'type'    => 'INT(11)',
            'null'    => false,
            'comment' => 'bbb',
        ),
    ),
    
    // index 設定
    'index'   => array(
        // key index
        'key'    => array(
            'name' => array( 'name' ),
        ),
        
        // unique index
        'unique' => array(
            'value' => array( 'value1', 'value2' ),
        ),
    ),
    
    // 論理削除 or 物理削除
    'delete'  => 'logical', // physical or logical [default = physical]
),
```

設定を更新したら configs/config.php の db_version も更新します。  
自動でテーブルの追加・更新が行われます。  
データの取得・挿入・更新・削除は以下のように行います。
```
// 取得
$this->app->db->select( 'test', array(
	'id'         => array( 'in', array( 1, 2, 3 ) ),
	'value1'     => array( 'like', 'tes%' ),
	'created_at' => array( '<', '2018-06-03' ),
	'value2'     => null,
	'value3'     => 3,
) );

// 挿入
$this->app->db->insert( 'test', array(
    'name'   => 'aaa',
    'value1' => 'bbb',
    'value3' => 100,
) );

// 更新
$this->app->db->update( 'test', array(
    'value2' => 'ccc',
), array(
    'id' => 4,
) );

// 削除
$this->app->db->delete( 'test', array(
    'id' => 4,
) );
```
select 以外は 内部でWordPress標準の関数を使用しているため、  
条件の指定の仕方は 'key' => 'value' (key = value) のみ可能です。  
select の条件指定はライブラリ側で構築しており、  
key = value  
```
key' => 'value'
```
key in ( val1, val2, val3 )
```
'key' => array( 'in', array( val1, val2, val3 ) )  
```
key like '%value%'
```
'key' => array( 'like', '%value%' )
```
などが指定可能です。

- configs/setting.php

設定例：
```
// priority => 詳細
'10' => array(

    // 設定グループ => 詳細
    'Performance' => array(
    
        // priority => 詳細
        '10' => array(
        
            // 設定名 => 詳細
            'minify_js'  => array(
                // 説明
                'label'   => 'Whether to minify js which generated by this plugin',
                // タイプ (bool or int or float or string)
                'type'    => 'bool', // [default = string]
                // デフォルト値
                'default' => true,
            ),
            'minify_css' => array(
                'label'   => 'Whether to minify css which generated by this plugin',
                'type'    => 'bool',
                'default' => true,
            ),
        ),
    ),
),
```

設定ページで設定可能になります。  
プログラムで使用するには以下のようにします。
```
$this->apply_filters( 'minify_js' ) // true or false

if ( $this->apply_filters( 'minify_js' ) ) {
    // ...
}
```

- configs/filter.php  
今後ドキュメント追加予定

- configs/slug.php  
今後ドキュメント追加予定

- configs/capability.php  
今後ドキュメント追加予定

## 画面の追加

- src/classes/controllers/admin に PHP ファイル (例：test.php) を追加
```
<?php

namespace Example\Classes\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

class Test extends \Technote\Classes\Controllers\Admin\Base {

	// タイトル
	public function get_page_title() {
		return 'Test';
	}

	// GET の時に行う動作
	protected function get_action() {

	}

	// POST の時に行う動作
	protected function post_action() {
		$aaa = $this->app->input->post( 'aaa' );
		// ... 
	}

    // GET, POST 共通で行う動作
	protected function common_action() {
        // wp_enqueue_script('media-upload');
	}

	// view に渡す変数設定
	public function get_view_args() {
	    return array(
	        'test' => 'aaaa',
	    );
	}
}
```

POST の時に行う動作は事前にnonce checkが行われます。

- src/views/admin に PHP ファイル (例：test.php) を追加
```
<?php

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
/** @var string $test */
?>

<?php $instance->form( 'open', $args ); ?>

<?php $instance->h( $test ); ?>
<?php $instance->form( 'input/submit', $args, array(
	'name'  => 'update',
	'value' => 'Update',
	'class' => 'button-primary'
) ); ?>

<?php $instance->form( 'close', $args ); ?>
```

- $instance
	- h：esc_html
	- dump：var_dump
	- id
	- form
	- url
	- img

- ヘルプの追加
	- src/classes/controllers/admin に追加した上記 PHP ファイル に以下を追記
```
protected function get_help_contents() {
    return array(
        array(
            'title' => 'Test',
            'view'  => 'test',
        )
    );
}
```

-
	- src/views/admin/help に PHP ファイル (例：test.php) を追加
```
<?php

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
?>

test
```

## API の追加
今後ドキュメント追加予定

## filter の追加
今後ドキュメント追加予定

## cron の追加
今後ドキュメント追加予定

## テストの追加

- PHPUnitの追加  
```composer require --dev phpunit/phpunit```

- src/classes/tests に PHP ファイル (例：sample.php) を追加
```
<?php

namespace Example\Classes\Tests;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Sample
 * @package Example\Classes\Tests
 */
class Sample extends \Technote\Classes\Tests\Base {

	public function test_sample1() {
		$this->assertEquals( 2, 1 + 1 );
	}

	public function test_sample2() {
		$this->assertEquals( 1, 1 + 1 );
	}

}
```

- 管理画面から実行

![test1](https://raw.githubusercontent.com/technote-space/wordpress-plugin-base/images/test1.png)
![test2](https://raw.githubusercontent.com/technote-space/wordpress-plugin-base/images/test2.png)

## サンプルプラグイン
[関連記事提供用プラグイン](https://github.com/technote-space/wp-related-post-jp)  
[Contact Form 7 拡張用プラグイン](https://github.com/technote-space/contact-form-7-huge-file-upload)  
[Marker Animation プラグイン](https://github.com/technote-space/marker-animation) 

# Author

[technote-space](https://github.com/technote-space)
