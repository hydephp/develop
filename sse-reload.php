<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Cache-Control: no-store');

header('Access-Control-Allow-Origin: *');

ob_end_clean();

while (true) {
    $data = (file_get_contents('sse-buffer') ?: '');

    if ($data) {
        echo "data: $data\n\n";
    }

    unlink('sse-buffer');

    if (ob_get_contents()) {
        ob_end_clean();
    }

    if (connection_aborted()) {
        break;
    }

    sleep(1);
}

// To trigger: Run: file_put_contents('sse-buffer', 'refresh');

// and use script

//<script>const source=new EventSource("http://localhost:8082");source.addEventListener("open",(function(e){console.log("Connection open")})),source.addEventListener("message",(function(e){const o=e.data;console.log("Received data:",o),"refresh"===o&&window.location.reload()})),source.addEventListener("error",(function(e){console.error("EventSource error",e)}));</script>
