<?php

/**
 * parses the XML response from the API
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Poll {

	/**
	 * raw xml
	 *
	 * @var string
	 */
	protected $xml = '';

	/**
	 * data object
	 *
	 * @var Not_So_Simple_XML
	 */
	protected $data = NULL;

	/**
	 * constructor
	 *
	 * @param string $xml
	 */
	public function __construct( $xml ) {

		$this->xml = $xml;
		$this->data = simpleXML_load_string( $xml, 'Not_So_Simple_XML', LIBXML_NOCDATA );
	}

	/**
	 * get the polls title
	 *
	 * @return string
	 */
	public function get_title() {

		return ( string ) $this->data->title;
	}

	/**
	 * get description
	 *
	 * @return string
	 */
	public function get_description() {

		return ( string ) $this->data->description;
	}

	/**
	 * get location
	 *
	 * @return string
	 */
	public function get_location() {

		return ( string ) $this->data->location;
	}

	/**
	 * get last activity date
	 *
	 * @return string iso-formated datetime string
	 */
	public function get_last_activity_date() {

		return ( string ) $this->data->latestChange;
	}

	/**
	 * get initiator
	 *
	 * @return array ( name, mail, user_id )
	 */
	public function get_initiator() {

		return ( array ) $this->data->initiator;
	}

	/**
	 * returns true, if the current poll is a datepoll
	 *
	 * @return bool
	 */
	public function is_date_poll() {

		 return isset( $this->data->options->option[ 'date' ] );
	}

	/**
	 * returns true if the current poll is a datepoll with time options
	 *
	 * @return bool
	 */
	public function is_datetime_poll() {

		return isset( $this->data->options->option[ 'dateTime' ] );
	}

	/**
	 * get the date options by months and days
	 *
	 * @param bool $flat
	 * @return array
	 */
	public function get_date_options() {

		if ( ! $this->is_date_poll() && ! $this->is_datetime_poll() )
			return array();

		$dates = array();
		$i = 0;
		foreach ( $this->data->options->option as $option ) {
			$date  = isset( $option[ 'dateTime' ] )
				? new DateTime( $option[ 'dateTime' ] )
				: new DateTime( $option[ 'date' ] );
			$month = $date->format( 'Y-m' );
			$day   = $date->format( 'Y-m-d' ); # just for internal use
			if ( empty( $dates[ $month ] ) )
				$dates[ $month ] = array();

			if ( empty( $dates[ $month ][ $day ] ) )
				$dates[ $month ][ $day ] = array();

			$dates[ $month ][ $day ][] = array(
				'date'  => $date,
				'count' => $this->get_option_count( $i )
			);
			$i++;
		}

		return $dates;
	}

	/**
	 * get all options
	 *
	 * @return array
	 */
	public function get_options() {

		$options = current( ( array ) $this->data->options );
		foreach ( $options as &$o ) {
			if ( is_string( $o ) )
				continue;
			elseif ( isset( $o[ 'date' ] ) )
				$o = ( string ) $o[ 'date' ];
			elseif ( isset( $o[ 'dateTime' ] ) )
				$o = ( string ) $o[ 'dateTime' ];
			else
				$o = ( string ) $o;
		}

		return $options;
	}

	/**
	 * get the sum of votes for an option
	 *
	 * @param int $index
	 * @return int
	 */
	public function get_option_count( $index ) {

		$yes = 0;
		$maybe = 0;
		$participants = $this->get_participants();
		foreach ( $this->get_participants() as $p ) {
			switch ( $p[ 'options' ][ $index ] ) {
				case '1' :
					$yes++;
				break;

				case '2' :
					$maybe++;
				break;
			}
		}

		return array( 'yes' => $yes, 'maybe' => $maybe );
	}

	/**
	 * get options sorted by popularity
	 *
	 * @return array
	 */
	public function get_top_options() {

		$options = $this->get_options();
		foreach ( $options as $index => $option ) {
			$options[ $option ] = $this->get_option_count( $index );
			unset( $options[ $index ] );
		}
		uasort( $options, 'doodle_option_sort_callback' );

		return $options;
	}

	/**
	 * get the count number of participants
	 *
	 * @return int
	 */
	public function get_participants_count() {

		return ( int ) $this->data->participants[ 'nrOf' ];
	}

	/**
	 * get the participants
	 *
	 * @return array
	 */
	public function get_participants() {

		$participants = array();
		foreach ( $this->data->participants->participant as $participant ) {
			$p = array(
				'id'      => ( string ) $participant->id,
				'name'    => ( string ) $participant->name,
				'userID'  => ( string ) $participant->userID,
				'options' => current( ( array ) $participant->preferences )
			);
			$participants[] = $p;
		}

		return $participants;
	}

	/**
	 * hackers best
	 */
	public function dump() {
		var_dump( $this->data );
	}
}
