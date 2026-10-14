<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Data access for branch offices and their schedule assignments.
 * All queries go through $wpdb->prepare / insert / update / delete.
 */
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Only table names (built from $wpdb->prefix) are interpolated; every value goes through $this->wpdb->prepare(), which the sniff cannot follow through a property.
class BranchOfficeService {

	private $tableName      = '';
	private $tableNameChild = '';
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->tableName      = $wpdb->prefix . 'wdlat_branch_offices';
		$this->tableNameChild = $wpdb->prefix . 'wdlat_branch_schedule';
		$this->wpdb           = $wpdb;
	}

	public function getList() {
		$tableName = $this->tableName;
		return $this->wpdb->get_results( "SELECT * FROM $tableName" );
	}

	public function get( $id, $includeSchedule = false ) {
		$tableName = $this->tableName;
		$res       = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $tableName WHERE id = %d", absint( $id ) ) );
		if ( count( $res ) > 0 ) {
			if ( $includeSchedule ) {
				$res[0]->schedules = $this->getSchedule( $res[0]->id );
			}
			return $res[0];
		}
		return null;
	}

	public function getSchedule( $office_id ) {
		$tableNameChild = $this->tableNameChild;
		$tableSchedule  = $this->wpdb->prefix . 'wdlat_schedules';
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT os.id as idmain, sch.* FROM $tableNameChild as os
                INNER JOIN $tableSchedule as sch  ON os.schedule_id = sch.id
                WHERE os.office_id = %d",
				absint( $office_id )
			)
		);
	}

	public function save( $branchOffice ) {
		$data = array(
			'name'        => sanitize_text_field( $branchOffice['name'] ?? '' ),
			'description' => sanitize_text_field( $branchOffice['description'] ?? '' ),
			'phone'       => sanitize_text_field( $branchOffice['phone'] ?? '' ),
			'address'     => sanitize_text_field( $branchOffice['address'] ?? '' ),
			'latitude'    => (float) ( $branchOffice['latitude'] ?? 0 ),
			'longitude'   => (float) ( $branchOffice['longitude'] ?? 0 ),
			'kmlfile'     => sanitize_text_field( $branchOffice['kmlfile'] ?? '' ),
		);
		$id   = absint( $branchOffice['id'] ?? 0 );
		if ( $id > 0 ) {
			$data['state'] = isset( $branchOffice['state'] ) ? 1 : 0;
			$this->wpdb->update( $this->tableName, $data, array( 'id' => $id ) );
		} else {
			$this->wpdb->insert( $this->tableName, $data );
			$id = $this->wpdb->insert_id;
		}

		if ( isset( $branchOffice['schedules'] ) ) {
			$oldSchedules = $this->getSchedule( $id );
			$ids          = array_map(
				function ( $schedule ) {
					return absint( $schedule['id'] ?? 0 );
				},
				$branchOffice['schedules']
			);
			if ( isset( $oldSchedules ) ) {
				foreach ( $oldSchedules as $oldSchedule ) {
					if ( ! in_array( (int) $oldSchedule->idmain, $ids ) ) {
						$this->deleteSchedule( $oldSchedule->idmain );
					}
				}
			}

			foreach ( $branchOffice['schedules'] as $schedule ) {
				$schedule['office_id'] = $id;
				$this->saveSchedule( $schedule );
			}
		}
		if ( $this->wpdb->last_error ) {
			error_log( 'woo-delivery-lat: ' . $this->wpdb->last_error );
		}
		return true;
	}

	public function deleteSchedule( $schedule_office_id ) {
		$this->wpdb->delete( $this->tableNameChild, array( 'id' => absint( $schedule_office_id ) ) );
		return true;
	}

	public function saveSchedule( $office_schedule ) {
		$data = array(
			'schedule_id' => absint( $office_schedule['schedule_id'] ?? 0 ),
			'office_id'   => absint( $office_schedule['office_id'] ?? 0 ),
		);
		$id   = absint( $office_schedule['id'] ?? 0 );
		if ( $id > 0 ) {
			$this->wpdb->update( $this->tableNameChild, $data, array( 'id' => $id ) );
		} else {
			$this->wpdb->insert( $this->tableNameChild, $data );
		}
		if ( $this->wpdb->last_error ) {
			error_log( 'woo-delivery-lat: ' . $this->wpdb->last_error );
		}
		return true;
	}

	public function delete( $id ) {
		$id = absint( $id );
		$this->wpdb->delete( $this->tableNameChild, array( 'office_id' => $id ) );
		$this->wpdb->delete( $this->tableName, array( 'id' => $id ) );
		return true;
	}
}
