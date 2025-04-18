Есть файл html:
<!DOCTYPE html>
<html class="notranslate" translate="no" data-prefers-color-scheme="dark" lang="en"
    style="font-size: 54.8px; --header-height: 80px; --aliyun-zoom-ratio: 1; --aliyun-imgbox-height: 300px;">
<div class="blind-comp-wrap">
    <div class="row header" data-blind="37">
        <div class="col">Lv</div>
        <div class="col">Blinds</div>
        <div class="col">Ante</div>
        <div class="col">TimeBank</div>
        <div class="col">Mins</div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>1</i><span class="purple">Re-Entry</span></div>
        <div class="col">50/100</div>
        <div class="col">15</div>
        <div class="col">15s x 2</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>2</i><span class="purple">Re-Entry</span></div>
        <div class="col">60/120</div>
        <div class="col">18</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>3</i><span class="purple">Re-Entry</span></div>
        <div class="col">70/140</div>
        <div class="col">21</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
</div>
</html>

Нужно написать код на PHP 8.2, используя XPath распаристь данные и добавить имеющийся файл json вот так:
{
"blinds": [
{
"level": 1,
"blind": "50/100",
"ante": 15,
"timebank": "15s x 2",
"mins": 7
},
{
"level": 2,
"blind": "60/120",
"ante": 18,
"timebank": "-",
"mins": 7
},
{
"level": 3,
"blind": "70/140",
"ante": 21,
"timebank": "-",
"mins": 7
},

}



<?php
// PHP 8.2 required for potential newer syntax/features, although core logic works in older versions.

// The HTML content provided by the user
$htmlContent = <<<HTML
<!DOCTYPE html>
<html class="notranslate" translate="no" data-prefers-color-scheme="dark" lang="en"
    style="font-size: 54.8px; --header-height: 80px; --aliyun-zoom-ratio: 1; --aliyun-imgbox-height: 300px;">
<div class="blind-comp-wrap">
    <div class="row header" data-blind="37">
        <div class="col">Lv</div>
        <div class="col">Blinds</div>
        <div class="col">Ante</div>
        <div class="col">TimeBank</div>
        <div class="col">Mins</div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>1</i><span class="purple">Re-Entry</span></div>
        <div class="col">50/100</div>
        <div class="col">15</div>
        <div class="col">15s x 2</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>2</i><span class="purple">Re-Entry</span></div>
        <div class="col">60/120</div>
        <div class="col">18</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>3</i><span class="purple">Re-Entry</span></div>
        <div class="col">70/140</div>
        <div class="col">21</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
</div>
</html>
HTML;

// Path to your existing JSON file (optional, if you need to merge)
// $jsonFilePath = 'path/to/your/existing_data.json';
// $existingData = [];
// if (file_exists($jsonFilePath)) {
//     $jsonContent = file_get_contents($jsonFilePath);
//     $existingData = json_decode($jsonContent, true);
//     if (json_last_error() !== JSON_ERROR_NONE) {
//         die("Error decoding existing JSON: " . json_last_error_msg());
//     }
// }


// Create a new DOMDocument instance
$dom = new DOMDocument();

// Suppress errors/warnings due to potentially imperfect HTML
libxml_use_internal_errors(true);

// Load the HTML content
// Use mb_convert_encoding to ensure correct UTF-8 handling if needed
$dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

// Clear any libxml errors
libxml_clear_errors();

// Create a new DOMXPath instance
$xpath = new DOMXPath($dom);

// XPath query to select all data rows
$dataRows = $xpath->query("//div[contains(@class, 'row') and contains(@class, 'data')]");

$parsedBlinds = [];

if ($dataRows !== false) {
    foreach ($dataRows as $row) {
        // Use evaluate with 'string()' to directly get text content, handling potential nulls
        // Using context node ($row) for relative queries
        $levelStr = trim($xpath->evaluate('string(./div[@class="col"][1]/i)', $row));
        $blindStr = trim($xpath->evaluate('string(./div[@class="col"][2])', $row));
        $anteStr = trim($xpath->evaluate('string(./div[@class="col"][3])', $row));
        $timebankStr = trim($xpath->evaluate('string(./div[@class="col"][4])', $row));
        $minsStr = trim($xpath->evaluate('string(./div[@class="col"][5])', $row));

        // Basic validation and type casting
        $level = filter_var($levelStr, FILTER_VALIDATE_INT);
        $ante = filter_var($anteStr, FILTER_VALIDATE_INT);
        $mins = filter_var($minsStr, FILTER_VALIDATE_INT);

        // Add to array only if level is a valid integer
        if ($level !== false) {
             $parsedBlinds[] = [
                'level' => $level,
                'blind' => $blindStr,
                // Use $ante directly as it's false if not a valid int, null if empty, or the int value
                'ante' => $ante === false || $anteStr === '-' ? ($anteStr === '-' ? $anteStr : null) : $ante,
                'timebank' => $timebankStr,
                 // Use $mins directly like $ante
                'mins' => $mins === false || $minsStr === '-' ? ($minsStr === '-' ? $minsStr : null) : $mins,
            ];
        }
    }
}

// --- Merging with existing JSON (if applicable) ---
// This example assumes the new data should overwrite or be the only 'blinds' data.
// If you need more complex merging logic (e.g., appending), adjust this section.

$finalData = [
    'blinds' => $parsedBlinds
];

// --- Alternative: Merging if existing data should be preserved ---
// // Ensure 'blinds' key exists in existing data
// if (!isset($existingData['blinds']) || !is_array($existingData['blinds'])) {
//     $existingData['blinds'] = [];
// }
// // Append new blinds (or replace based on level, etc. - requires more logic)
// $existingData['blinds'] = array_merge($existingData['blinds'], $parsedBlinds); // Simple append
// $finalData = $existingData;


// Encode the final data structure to JSON
$jsonOutput = json_encode($finalData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// Output the JSON
header('Content-Type: application/json; charset=utf-8');
echo $jsonOutput;

// --- Optional: Save to file ---
// file_put_contents($jsonFilePath, $jsonOutput);
// echo "\nJSON data saved to " . $jsonFilePath;

?>