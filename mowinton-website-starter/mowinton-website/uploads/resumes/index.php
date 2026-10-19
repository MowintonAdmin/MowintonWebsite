<?php
http_response_code(403);
header('Content-Type: text/plain');
exit('403 Forbidden: Direct directory browsing and file access is prohibited.');
