<?php
/**
 * Technote Configs Db
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return array(

	'test' => array(
		'id'      => 'test_id',     // optional [default = $table_name . '_id']
		'columns' => array(
			'name'   => array(
				'name'     => 'name',          // optional
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
		'index'   => array(
			'key'    => array( // key index
				'name' => array( 'name' ),
			),
			'unique' => array( // unique index
				'value' => array( 'value1', 'value2' ),
			),
		),
		'delete'  => 'logical', // physical or logical [default = logical]
	),

);

//add table settings
//global $technote_db;
////sample
//$technote_db->add_table(
//	"sample", "sample",
//	array(
//		"sample" => array( "sample", "VARCHAR(32)", "NOT NULL" ),
//		"index_field" => array( "sample", "INT(11)", "NOT NULL" ),
//	),
//	array( 'index_field' )
//);

//
//$technote_db->add_table(
//	"tweet", "tweet",
//	array(
//		"tweet_at"  => array( "tweet_at", "DATETIME", "NOT NULL" ),
//		"tweet_id"  => array( "tweet_id", "VARCHAR(32)", "NOT NULL" ),
//		"text"      => array( "text", "TEXT", "NOT NULL" ),
//		"user_id"   => array( "user_id", "VARCHAR(32)", "NOT NULL" ),
//		"search"    => array( "search", "VARCHAR(255)", "NOT NULL" ),
//		"processed" => array( "processed", "TINYINT(1)", "NOT NULL" )
//	),
//	array( "tweet_id" )
//);
//
//$technote_db->add_table(
//	"tweet_word", "tweet_word",
//	array(
//		"word"  => array( "word", "VARCHAR(255)", "NOT NULL" ),
//		"count" => array( "number", "INT(11)", "NOT NULL" ),
//	),
//	array( "word" )
//);
//
//$technote_db->add_table(
//	"tweet_data", "tweet_data",
//	array(
//		"word_id"  => array( "word_id", "VARCHAR(32)", "NOT NULL" ),
//		"tweet_id" => array( "tweet_id", "VARCHAR(32)", "NOT NULL" ),
//		"order"    => array( "rank", "INT(11)", "NOT NULL" ),
//	),
//	array( "word_id", "tweet_id" )
//);
//
////$technote_db->add_table(
////	"amazon_product", "amazon_product",
////	array(
////
////	)
////);
//
//$technote_db->add_table(
//	"search_history", "search_history",
//	array(
//		"words" => array( "words", "VARCHAR(1023)", "NOT NULL" ),
//		"hits"  => array( "hits", "INT(11)", "NOT NULL" ),
//	),
//	array( "words" )
//);
//
//$technote_db->add_table(
//	"search_history_word", "search_history_word",
//	array(
//		"history_id" => array( "history_id", "VARCHAR(32)", "NOT NULL" ),
//		"word_id"    => array( "word_id", "VARCHAR(32)", "NOT NULL" ),
//	),
//	array( "history_id", "word_id" )
//);
//
//$technote_db->add_table(
//	"search_result", "search_result",
//	array(
//		"history_id" => array( "history_id", "VARCHAR(32)", "NOT NULL" ),
//		"product_id" => array( "product_id", "VARCHAR(32)", "NOT NULL" ),
//		"order"      => array( "rank", "INT(11)", "NOT NULL" ),
//	),
//	array( "history_id", "product_id" )
//);
//
//$technote_db->add_table(
//	"rakuten_product", "rakuten_product",
//	array(
//		"name"           => array( "name", "VARCHAR(255)", "NOT NULL" ),
//		"catchcopy"      => array( "catchcopy", "VARCHAR(255)", "NOT NULL" ),
//		"code"           => array( "code", "VARCHAR(64)", "NOT NULL" ),
//		"price"          => array( "price", "INT(11)", "NOT NULL" ),
//		"caption"        => array( "caption", "TEXT", "NOT NULL" ),
//		"url"            => array( "url", "VARCHAR(1023)", "NOT NULL" ),
//		"shop_name"      => array( "shop_name", "VARCHAR(255)", "NOT NULL" ),
//		"shop_code"      => array( "shop_code", "VARCHAR(64)", "NOT NULL" ),
//		"shop_url"       => array( "shop_url", "VARCHAR(1023)", "NOT NULL" ),
//		'review_count'   => array( "review_count", "INT(11)", "NOT NULL" ),
//		"review_average" => array( "review_average", "FLOAT", "NOT NULL" ),
//
//		"postage_flag" => array( "postage_flag", "TINYINT(1)", "NOT NULL" ),
//		"tax_flag"     => array( "tax_flag", "TINYINT(1)", "NOT NULL" ),
//	),
//	array( "word_id", "tweet_id", "code" )
//);
//
//$technote_db->add_table(
//	"rakuten_product_image", "rakuten_product_image",
//	array(
//		"product_id" => array( "product_id", "VARCHAR(32)", "NOT NULL" ),
//		"image_id"   => array( "image_id", "INT(11)", "NOT NULL" ),
//		"url"        => array( "url", "VARCHAR(1023)", "NOT NULL" ),
//		"order"      => array( "rank", "INT(11)", "NOT NULL" ),
//	),
//	array( "product_id" )
//);
//
//$technote_db->add_table(
//	"data_cache", "data_cache",
//	array(
//		"key"        => array( "search_key", "VARCHAR(255)", "NOT NULL" ),
//		"param1"     => array( "param1", "VARCHAR(32)", "NOT NULL" ),
//		"param2"     => array( "param2", "VARCHAR(32)", "NOT NULL" ),
//		"param3"     => array( "param3", "VARCHAR(32)", "NOT NULL" ),
//		"param4"     => array( "param4", "VARCHAR(32)", "NOT NULL" ),
//		"param5"     => array( "param5", "VARCHAR(32)", "NOT NULL" ),
//		"cache_data" => array( "cache_data", "LONGTEXT", "NOT NULL" ),
//		"hash"       => array( "hash", "VARCHAR(70)", "NOT NULL" ),
//	),
//	array( "key", "hash" )
//);
//
