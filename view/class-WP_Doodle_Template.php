<?php

/**
 * provide template functions to create templates
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Template {

	/**
	 * the poll
	 *
	 * @var WP_Doodle_Poll
	 */
	protected $poll = NULL;



	/**
	 * setup the data
	 *
	 * @var WP_Doodle_poll
	 */
	public function __construct( $poll ) {

		$this->poll = $poll;
	}

	/**
	 * returns the poll html table
	 *
	 * @return string
	 */
	public function as_html() {

		$table  = '<table>';
		$table .= $this->get_table_header( 1 );
		$table .= $this->get_table_body();
		$table .= '</table>';

		return $table;

	}

	/**
	 * builds the head of the table
	 *
	 * @param int $col_offset
	 * @return string
	 */
	protected function get_table_header( $col_offset = 0 ) {

		$header = '<thead>';
		if ( $this->poll->is_date_poll() || $this->poll->is_datetime_poll() ) {
			$options    = $this->poll->get_date_options();
			$days_left  = array();
			$times_left = array();
			$header .= '<tr>' . str_repeat( '<td></td>', $col_offset );
			foreach ( $options as $month => $days ) {
				$colspan = 0;
				foreach ( $days as $day ) {
					$colspan += count( $day ); # count the time-option on one day
				}
				$day = current( $days );
				$th = sprintf(
					'<th colspan="%1$d" class="%2$s">%3$s</th>',
					$colspan,
					apply_filters( 'dp_poll_header_class', 'dp-poll-option-month', 'poll-header-month' ),
					$day[ 0 ][ 'date' ]->format( apply_filters( 'dp_poll_header_month_format', 'F Y' ) )
				);
				$header .= $th;
				$days_left = array_merge( $days_left, $days );
			}

			$header .= '</tr><tr>';
			$header .= $this->poll->is_datetime_poll() || 1 > $this->poll->get_participants_count()
					? str_repeat( '<td></td>', $col_offset )
					: '<td>' . sprintf( __( '%d Participants', 'doodle_polls' ), $this->poll->get_participants_count() ) . '</td>'
						. str_repeat( '<td></td>', $col_offset - 1 );

			foreach ( $days_left as $day => $times ) {
				$colspan = count( $times );
				$th = sprintf(
					'<th colspan="%1$d" class="%2$s"%4$s>%3$s</th>',
					$colspan,
					apply_filters( 'dp_poll_header_class', 'dp-poll-option-day', 'poll-header-day' ),
					$times[ 0 ][ 'date' ]->format( apply_filters( 'dp_poll_header_day_format', 'D d' ) ),
					$this->poll->is_datetime_poll() ? '' : ' scope="col"'
				);
				$header .= $th;
				$times_left = array_merge( $times_left, $times );
			}
			$header .= '</tr>';

			if ( $this->poll->is_datetime_poll() ) {
				$header .= '<tr>';
				if ( 0 < $this->poll->get_participants_count() ) {
					$count = $this->poll->get_participants_count();
					$header .= '<td>'
						. sprintf( _n( '%d Participant', '%d Participants', $count, 'doodle_polls' ), $count )
						. '</td>'
						. str_repeat( '<td></td>', $col_offset - 1 );
				} else {
					$header .= str_repeat( '<td></td>', $col_offset );
				}
				foreach ( $times_left as $time ) {
					$th = sprintf(
						'<th class="%1$s" scope="col">%2$s</th>',
						apply_filters( 'dp_poll_header_class', 'dp-poll-option-time', 'poll-header-time' ),
						$time[ 'date' ]->format( apply_filters( 'dp_poll_header_time_format', 'H:i' ) )
					);
					$header .= $th;
				}
			}
			$header .= '</tr>';
		} else {
			$header .= '<tr>';
			$options = $this->poll->get_options();
			if ( 0 < $this->poll->get_participants_count() ) {
				$count = $this->poll->get_participants_count();
				$header .= '<td>'
					. sprintf( _n( '%d Participant', '%d Participants', $count, 'doodle_polls' ), $count )
					. '</td>'
					. str_repeat( '<td></td>', $col_offset - 1 );
			} else {
				$header .= str_repeat( '<td></td>', $col_offset );
			}
			foreach ( $options as $option ) {
				$header .= sprintf(
					'<th scope="col" class="%1$s">%2$s</th>',
					apply_filters( 'dp_poll_header_class', 'dp-poll-option', 'poll-header' ),
					$option
				);
			}
			$header .= '</tr>';
		}

		$header .= '</thead>';

		return apply_filters( 'dp_poll_header', $header, $this->poll, $col_offset );
	}

	/**
	 * get the participants as table
	 *
	 * @return string
	 */
	public function get_table_body() {

		$body = '<tbody>';
		$participants = $this->poll->get_participants();
		foreach ( $participants as $p ) {
			$tr  = '<tr>';
			$tr .= '<td>' . $p[ 'name' ] . '</td>';
			foreach ( $p[ 'options' ] as $option ) {
				$tr .= '<td>' . $option . '</td>';
			}
			$tr .= '</tr>';
			$body .= $tr;
		}
		$body .= '</tbody>';

		return $body;
	}

}
