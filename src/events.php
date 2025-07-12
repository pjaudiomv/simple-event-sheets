<?php

namespace SimpleEventSheets;

class Events {

	const HTTP_RETRIEVE_ARGS = array(
		'headers' => array(
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0 +SEL',
		),
		'timeout' => 601,
	);

	public function __construct() {
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'admin_menu', array( $this, 'createMenu' ) );
	}

	public function optional_get_events(): string {
		if ( ! get_option( 'simple_event_sheets_checkbox' ) ) {
			return '';
		}

		$sheet_id = esc_attr( get_option( 'simple_event_sheets_sheet_id' ) );
		$sheet_name = esc_attr( get_option( 'simple_event_sheets_sheet_name' ) );
		$google_api_key = esc_attr( get_option( 'simple_event_sheets_google_api_key' ) );

		$query_params = [ 'key' => $google_api_key ];
		$url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}/values/{$sheet_name}";

		$response = $this->get_remote_response( $url, $query_params );

		return 'error' === $response['status'] ? '' : $response['data'];
	}

	/**
	 * Render events shortcode with support for calendar view
	 */
	public function render_events( $atts = [] ): string {
		// Parse shortcode attributes with defaults from settings
		$default_view = get_option( 'simple_event_sheets_default_view', 'list' );
		$default_toggle = get_option( 'simple_event_sheets_show_view_toggle', 1 ) ? 'true' : 'false';

		$attributes = shortcode_atts(
			array(
				'view' => $default_view,
				'show_toggle' => $default_toggle,
			),
			$atts
		);

		// Sanitize attributes
		$view = sanitize_text_field( $attributes['view'] );
		$show_toggle = sanitize_text_field( $attributes['show_toggle'] );

		// Validate view parameter
		if ( ! in_array( $view, [ 'list', 'calendar' ], true ) ) {
			$view = $default_view;
		}

		// Validate show_toggle parameter
		if ( ! in_array( $show_toggle, [ 'true', 'false' ], true ) ) {
			$show_toggle = $default_toggle;
		}

		// Add container with data attributes for JavaScript
		$container_classes = 'simple-event-sheets-wrapper';
		$container_attrs = sprintf(
			'data-default-view="%s" data-show-toggle="%s"',
			esc_attr( $view ),
			esc_attr( $show_toggle )
		);

		return sprintf(
			'<div id="simple-event-sheets-container" class="%s" %s></div>',
			esc_attr( $container_classes ),
			$container_attrs
		);
	}

	/**
	 * Get script parameters including settings for JavaScript
	 */
	public function get_script_params( $atts = [] ): array {
		// Get global settings
		$default_view = get_option( 'simple_event_sheets_default_view', 'list' );
		$default_toggle = get_option( 'simple_event_sheets_show_view_toggle', 1 ) ? 'true' : 'false';
		$start_day = get_option( 'simple_event_sheets_calendar_start_day', 'sunday' );
		$max_events = get_option( 'simple_event_sheets_max_events_per_day', 3 );

		// Parse any shortcode attributes
		$attributes = shortcode_atts(
			array(
				'view' => $default_view,
				'show_toggle' => $default_toggle,
			),
			$atts
		);

		return [
			'SHEET_ID' => esc_js( get_option( 'simple_event_sheets_sheet_id' ) ),
			'SHEET_NAME' => esc_js( get_option( 'simple_event_sheets_sheet_name' ) ),
			'API_KEY' => esc_js( get_option( 'simple_event_sheets_google_api_key' ) ),
			'SHOW_PASSED_EVENTS' => esc_attr( get_option( 'simple_event_sheets_passed_events_checkbox' ) ),
			'DEFAULT_VIEW' => esc_js( $attributes['view'] ),
			'SHOW_TOGGLE' => esc_js( $attributes['show_toggle'] ),
			'CALENDAR_START_DAY' => esc_js( $start_day ),
			'MAX_EVENTS_PER_DAY' => absint( $max_events ),
			'EVENTS' => $this->optional_get_events(),
		];
	}

	/**
	 * Get events data for admin preview or testing (optional method)
	 */
	public function get_events_for_preview(): array {
		$events_json = $this->optional_get_events();

		if ( empty( $events_json ) ) {
			return [];
		}

		$data = json_decode( $events_json, true );
		if ( ! $data || ! isset( $data['values'] ) ) {
			return [];
		}

		// Convert to associative array format
		$headers = $data['values'][0] ?? [];
		$rows = array_slice( $data['values'], 1 );

		return array_map(
			function ( $row ) use ( $headers ) {
				return array_combine( $headers, array_pad( $row, count( $headers ), '' ) );
			},
			$rows
		);
	}

	private function get_remote_response( string $url, array $query_params = [] ): array {
		if ( ! empty( $query_params ) ) {
			$url .= '?' . http_build_query( $query_params );
		}
		$response = wp_remote_get( $url, self::HTTP_RETRIEVE_ARGS );

		if ( is_wp_error( $response ) ) {
			return [
				'status' => 'error',
				'message' => 'Error fetching data from server: ' . $response->get_error_message(),
			];
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return [
				'status' => 'error',
				'message' => 'Received empty data from server.',
			];
		}

		return [
			'status' => 'success',
			'data' => $data,
		];
	}
}
