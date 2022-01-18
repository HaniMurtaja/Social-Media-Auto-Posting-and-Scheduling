<?php

namespace FSPoster\App\Providers;

use wpdb;

/**
 * Class DB
 * @package FSPoster\App\Providers
 */
class DB
{
	const PLUGIN_DB_PREFIX = 'fs_';

	/**
	 * @return wpdb
	 */
	public static function DB ()
	{
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @param $tbName
	 *
	 * @return string
	 */
	public static function table ( $tbName )
	{
		return self::DB()->base_prefix . self::PLUGIN_DB_PREFIX . $tbName;
	}

	/**
	 * @param $tbName
	 *
	 * @return string
	 */
	public static function WPtable ( $tbName, $multisite = FALSE )
	{
		$multisitePrefix = '';
		if ( $multisite && is_multisite() && Helper::getBlogId() > 1 )
		{
			$multisitePrefix = Helper::getBlogId() . '_';
		}

		return self::DB()->base_prefix . $multisitePrefix . $tbName;
	}

	/**
	 * @param $table
	 * @param null $where
	 *
	 * @return mixed
	 */
	public static function fetch ( $table, $where = NULL )
	{
		$whereQuery = '';
		$argss      = [];
		$where      = is_numeric( $where ) && $where > 0 ? [ $where ] : $where;
		if ( ! empty( $where ) && is_array( $where ) )
		{
			$whereQuery = '';

			foreach ( $where as $filed => $value )
			{
				$filed      = $filed === 0 ? 'id' : $filed;
				$whereQuery .= ( $whereQuery === '' ? '' : ' AND ' ) . $filed . '=%s';
				$argss[]    = (string) $value;
			}

			$whereQuery = ' WHERE ' . $whereQuery;
		}

		if ( empty( $argss ) )
		{
			return DB::DB()->get_row( "SELECT * FROM " . DB::table( $table ) . $whereQuery, ARRAY_A );
		}

		return DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( $table ) . $whereQuery, $argss ), ARRAY_A );

	}

	/**
	 * @param $table
	 * @param null $where
	 *
	 * @return mixed
	 */
	public static function fetchAll ( $table, $where = NULL )
	{
		$whereQuery = '';
		$argss      = [];
		$where      = is_numeric( $where ) && $where > 0 ? [ $where ] : $where;
		if ( ! empty( $where ) && is_array( $where ) )
		{
			$whereQuery = '';

			foreach ( $where as $filed => $value )
			{
				$filed      = $filed === 0 ? 'id' : $filed;
				$whereQuery .= ( $whereQuery === '' ? '' : ' AND ' ) . $filed . '=%s';
				$argss[]    = (string) $value;
			}

			$whereQuery = ' WHERE ' . $whereQuery;
		}

		if ( empty( $argss ) )
		{
			return DB::DB()->get_results( "SELECT * FROM " . DB::table( $table ) . $whereQuery, ARRAY_A );
		}

		return DB::DB()->get_results( DB::DB()->prepare( "SELECT * FROM " . DB::table( $table ) . $whereQuery, $argss ), ARRAY_A );

	}

	public static function insertAll($table, $columns, $rows) {

		$table = DB::table( $table );
		$columnList = '`' . implode('`, `', $columns) . '`';

		$sql = "INSERT INTO `$table` ($columnList) VALUES\n";
		$placeholders = array();
		$data = array();

		while ($row = array_splice($rows, 0, 1)) {
			$rowPlaceholders = array();

			foreach ($row[0] as $value) {
				$data[] = $value;
				$rowPlaceholders[] = is_numeric($value) ? '%d' : '%s';
			}

			$placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
		}

		$sql .= implode(",\n", $placeholders);

		return self::DB()->query(self::DB()->prepare($sql, $data));
	}
}