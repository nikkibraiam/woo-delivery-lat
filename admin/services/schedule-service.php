<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Data access for schedules (opening hours) and their per-weekday details.
 * All queries go through $wpdb->prepare / insert / update / delete.
 */
class ScheduleService {

	private $tableName    = '';
	private $tableNameDet = '';
	private $wpdb;

	function __construct() {
		global $wpdb;
		$this->tableName    = $wpdb->prefix . 'wdlat_schedules';
		$this->tableNameDet = $wpdb->prefix . 'wdlat_schedules_details';
		$this->wpdb         = $wpdb;
	}

	function getList( $active = false ) {
		$tableName = $this->tableName;
		$sql       = "SELECT * FROM $tableName";
		if ( $active ) {
			$sql = $sql . ' WHERE state = 1';
		}
		return $this->wpdb->get_results( $sql );
	}

	function get( $id, $include_det = false ) {
		$tableName = $this->tableName;
		$res       = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $tableName WHERE id = %d", absint( $id ) ) );
		if ( sizeof( $res ) > 0 ) {
			if ( $include_det ) {
				$res[0]->details = $this->getDetails( $res[0]->id );
			}
			return $res[0];
		}
		return null;
	}

	function getDetails( $shedule_id ) {
		$tableName = $this->tableNameDet;
		return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $tableName WHERE schedule_id = %d", absint( $shedule_id ) ) );
	}


	function save( $schedule ) {
		$data = array(
			'name'        => sanitize_text_field( $schedule['name'] ?? '' ),
			'description' => sanitize_text_field( $schedule['description'] ?? '' ),
		);
		$id   = absint( $schedule['id'] ?? 0 );
		if ( $id > 0 ) {
			$data['state'] = isset( $schedule['state'] ) ? 1 : 0;
			$this->wpdb->update( $this->tableName, $data, array( 'id' => $id ) );
		} else {
			$this->wpdb->insert( $this->tableName, $data );
			$id = $this->wpdb->insert_id;
		}

		$details    = $schedule['details'] ?? array();
		$oldDetails = $this->getDetails( $id );
		$ids        = array_map(
			function ( $detail ) {
				return absint( $detail['id'] ?? 0 );
			},
			$details
		);
		if ( isset( $oldDetails ) ) {
			foreach ( $oldDetails as $oldDetail ) {
				if ( ! in_array( (int) $oldDetail->id, $ids ) ) {
					$this->deleteDetail( $oldDetail->id );
				}
			}
		}

		foreach ( $details as $detail ) {
			$detail['schedule_id'] = $id;
			$this->saveDetail( $detail );
		}
		if ( $this->wpdb->last_error ) {
			error_log( 'woo-delivery-lat: ' . $this->wpdb->last_error );
		}
		return true;
	}

	function saveDetail( $detail ) {
		$data = array(
			'schedule_id' => absint( $detail['schedule_id'] ?? 0 ),
			'week_day'    => absint( $detail['week_day'] ?? 0 ),
			'start_hour'  => $this->sanitizeTime( $detail['start_hour'] ?? '' ),
			'end_hour'    => $this->sanitizeTime( $detail['end_hour'] ?? '' ),
		);
		$id   = absint( $detail['id'] ?? 0 );
		if ( $id > 0 ) {
			$data['state'] = isset( $detail['state'] ) ? 1 : 0;
			$this->wpdb->update( $this->tableNameDet, $data, array( 'id' => $id ) );
		} else {
			$this->wpdb->insert( $this->tableNameDet, $data );
		}
		if ( $this->wpdb->last_error ) {
			error_log( 'woo-delivery-lat: ' . $this->wpdb->last_error );
		}
		return true;
	}

	private function sanitizeTime( $value ) {
		return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) ? $value : '00:00:00';
	}

	function deleteDetail( $id ) {
		$this->wpdb->delete( $this->tableNameDet, array( 'id' => absint( $id ) ) );
		return true;
	}

	function delete( $id ) {
		$id = absint( $id );
		$this->wpdb->delete( $this->wpdb->prefix . 'wdlat_branch_schedule', array( 'schedule_id' => $id ) );
		$this->wpdb->delete( $this->tableName, array( 'id' => $id ) );
		return true;
	}
}
