# Wordpress plugin base

Wordpress plugin 開発用のライブラリです。  
管理画面やAPIなどの追加や設定値の読み書き等を容易にする機能が用意されています。  


# インストール

```composer require technote/wordpress-plugin-base```


# 開発

## 基本設定
- configs/config.php  

|設定値|説明|
|---|---|
|plugin_version|プラグインのバージョンを指定します|
|db_version|DBの設定を変更したら更新します|
|twitter|ツイッターのアカウントを指定します（空で未使用）|
|github|Githubのアカウントを指定します（空で未使用）|
|contact_url|プラグインのお問い合わせ用のページのURLを指定します|

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
    'delete'  => 'logical', // physical or logical [default = logical]
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
if ( $this->apply_filters( 'minify_css' ) ) {
    // ...
}
```

- configs/filter.php

- configs/slug.php

- configs/capability.php


## 画面の追加

- classes/controllers/admin に PHP ファイルを追加
```
<?php

namespace Example\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

class Test extends \Technote\Controllers\Admin\Base {
	
	// タイトル
	public function get_page_title() {
		return 'Test';
	}
	
	// GET の時に行う動作
	public function get_action() {

	}
	
	// POST の時に行う動作
	public function post_action() {
		$aaa = $this->app->input->post( 'aaa' );
		// ... 
	}
	
	// view に渡す変数設定
	public function get_view_args() {
	    return array(
	        'test' => 'aaaa',
	    );
	}
}
```

- views/admin に PHP ファイルを追加
```
<?php

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
/** @var string $test */
?>

<?php $instance->h( $test ); ?>
```

- $instance
	- h：esc_html
	- dump：var_dump
	- id
	- form
	- url
	- img

- ヘルプの追加
	- classes/controllers/admin に追加した PHP に以下を追記
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
	- views/admin/help に PHP ファイルを追加
```
<?php

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
?>

test
```

## API の追加

## filterの追加

