<?php

namespace SimpleEventSheets;

class Events
{
    const HTTP_RETRIEVE_ARGS = array(
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0 +SEL'
        ),
        'timeout' => 601
    );

    public function __construct()
    {
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_menu', array($this, 'createMenu'));
    }

    public function optionalGetEvents(): string
    {
        if (!get_option('simple_event_sheets_checkbox')) {
            return '';
        }

        $sheet_id = get_option('simple_event_sheets_sheet_id');
        $sheet_name = get_option('simple_event_sheets_sheet_name');
        $google_api_key = get_option('simple_event_sheets_google_api_key');

        $queryParams = ['key' => $google_api_key];
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}/values/{$sheet_name}";

        $response = $this->getRemoteResponse($url, $queryParams);

        return $response['status'] === 'error' ? '' : $response['data'];
    }

    public function renderEvents($atts = [])
    {
        return '<div id="simple-event-sheets-container"></div>';
    }

    private function getRemoteResponse(string $url, array $queryParams = []): array
    {
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        $response = wp_remote_get($url, self::HTTP_RETRIEVE_ARGS);

        if (is_wp_error($response)) {
            return ['status' => 'error', 'message' => 'Error fetching data from server: ' . $response->get_error_message()];
        }

        $data = wp_remote_retrieve_body($response);

        if (empty($data)) {
            return ['status' => 'error', 'message' => 'Received empty data from server.'];
        }

        return ['status' => 'success', 'data' => $data];
    }
}
