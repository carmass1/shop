<?php
    require_once ('config.php');
    date_default_timezone_set ('America/Los_Angeles');

    function getStoreDataFromAPI ( $date ){
        $apiUrlEndpoint = 'https://api.fortnitetracker.com/v1/store';
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $apiUrlEndpoint);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array(
            'TRN-Api-Key:' . FNKEY
        ));
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ( $ch, CURLOPT_HEADER, FALSE);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec ( $ch );


        $storeFile = fopen ('shop_json/' . $date . '.json', 'w');
        fwrite ( $storeFile, $response );
        fclose ( $storeFile );
    }

    function getStoreData( $date ) {
        if ( !file_exists( 'shop_json/' . $date . '.json')) {
            getStoreDataFromAPI ( $date );
        }

        return json_decode ( file_get_contents('shop_json/' .$date. '.json'), true);
    }

    function getStoreSortedData ($date){
        $items  = getStoreData ($date);

        $sortedItems = array(
			'BRWeeklyStorefront' => array(
				'info' => array(
					'title' => 'FEATURED ITEMS'
				),
				'items' => array()
			),
			'BRDailyStorefront' => array(
				'info' => array(
					'title' => 'DAILY ITEMS'
				),
				'items' => array()
			)
		);

            foreach ($items as $item) {
                $itemUrlName = strtolower($item['name']);
                $itemUrlName = str_replace (' ', '-', $itemUrlName);
                $item ['link_to_fb_item'] = 'https://fortnitetracker.com/shop' . $item['manifestId'] . '/' . $itemUrlName;
                $sortedItems[$item['storeCategory']]['items'][] = $item;
            }

            return $sortedItems;
    }

    function getStoreDate(){
        $date = date('Y-m-d');
        $tomorrowDate = date('Y-m-d', strtotime (' +1 day'));
        if (date ('G')>=18) {
            $date = $tomorrowDate;
        }

        if (isset( $_GET['date']) && $date != $_GET['date']) {
            $pastFiles = getStoreJsonFiles();

            foreach ($pastFiles as $file) {
                if ($file['date']== $_GET['date']) {
                    $date = $file['date'];
                    break;
                }
            }
        }
        return $date;
    }

    function getStoreJsonFiles(){
        $allFiles = scandir ('shop_json');
        rsort($allFiles);

        $validFiles = array();
        foreach ($allFiles as $file) {
            $namePieces = explode('.', $file);
            if (isset($namePieces[1]) && 'json' == $namePieces[1]) {
                $validFiles[] = array(
                    'file' => $file,
                    'date' => $namePieces[0],
                    'link_json' => 'http://' . $_SERVER['HTTP_HOST'] . '/shopfortnite/shop_json' . $file,
                    'link_store' => 'http://' . $_SERVER['HTTP_HOST'] . '/shopfortnite/?date=' . $namePieces[0]
                );
            }
        }
        return $validFiles;
    }