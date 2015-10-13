<?php
/*
Copyright 2009-2015 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Output_Html_DB_Callers extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 30 );
	}

	public function output() {

		$data = $this->collector->get_data();

		if ( empty( $data['types'] ) ) {
			return;
		}

		$total_time  = 0;
		$span = count( $data['types'] ) + 2;

		echo '<div class="qm qm-half" id="' . esc_attr( $this->collector->id() ) . '">';
		echo '<table cellspacing="0" class="qm-sortable">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="' . absint( $span ) . '">' . esc_html( $this->collector->name() ) . '</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>' . esc_html_x( 'Caller', 'Query caller', 'query-monitor' ) . '</th>';

		foreach ( $data['types'] as $type_name => $type_count ) {
			echo '<th class="qm-num">' . $type_name . $this->build_sorter() . '</th>';
		}

		echo '<th class="qm-num qm-sorted-desc">' . __( 'Time', 'query-monitor' ) . $this->build_sorter() . '</th>';
		echo '</tr>';
		echo '</thead>';

		if ( !empty( $data['times'] ) ) {

			echo '<tbody>';

			foreach ( $data['times'] as $row ) {
				$total_time  += $row['ltime'];
				$stime = number_format_i18n( $row['ltime'], 4 );

				echo '<tr>';
				echo '<td class="qm-ltr">' . esc_html( $row['caller'] ) . '</td>';

				foreach ( $data['types'] as $type_name => $type_count ) {
					if ( isset( $row['types'][$type_name] ) ) {
						echo "<td class='qm-num'>" . esc_html( number_format_i18n( $row['types'][$type_name] ) ) . '</td>';
					} else {
						echo "<td valign='top' class='qm-num'>&nbsp;</td>";
					}
				}

				echo '<td class="qm-num">' . esc_html( $stime ) . '</td>';
				echo '</tr>';

			}

			echo '</tbody>';
			echo '<tfoot>';

			$total_stime = number_format_i18n( $total_time, 4 );

			echo '<tr>';
			echo '<td>&nbsp;</td>';

			foreach ( $data['types'] as $type_name => $type_count ) {
				echo '<td class="qm-num">' . esc_html( number_format_i18n( $type_count ) ) . '</td>';
			}

			echo '<td class="qm-num">' . esc_html( $total_stime ) . '</td>';
			echo '</tr>';

			echo '</tfoot>';

		} else {

			echo '<tbody>';
			echo '<tr>';
			echo '<td colspan="3" style="text-align:center !important"><em>' . esc_html__( 'none', 'query-monitor' ) . '</em></td>';
			echo '</tr>';
			echo '</tbody>';

		}

		echo '</table>';
		echo '</div>';

	}

	public function admin_menu( array $menu ) {

		if ( $dbq = QM_Collectors::get( 'db_queries' ) ) {
			$dbq_data = $dbq->get_data();
			if ( isset( $dbq_data['times'] ) ) {
				$menu[] = $this->menu( array(
					'title' => esc_html__( 'Queries by Caller', 'query-monitor' )
				) );
			}
		}
		return $menu;

	}

}

function register_qm_output_html_db_callers( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'db_callers' ) ) {
		$output['db_callers'] = new QM_Output_Html_DB_Callers( $collector );
	}
	return $output;
}

add_filter( 'qm/outputter/html', 'register_qm_output_html_db_callers', 30, 2 );
