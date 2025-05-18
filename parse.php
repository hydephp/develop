<?php

/**
 * CSV Parser for Changelog
 * Parses input.csv into a structured Markdown file with categories and headings.
 */

// Function to format details and handle PHP_EOL strings
function formatDetails(string $details): string
{
    if (empty($details)) {
        return '';
    }

    // Convert GitHub PR URLs to markdown links
    $pattern = '~https://github\.com/hydephp/develop/pull/(\d+)~';
    $details = preg_replace($pattern, '[#$1](https://github.com/hydephp/develop/pull/$1)', $details);

    if (strpos($details, 'PHP_EOL') !== false) {
        $items = explode('PHP_EOL', $details);
        $formatted = "\n";
        foreach ($items as $item) {
            $item = trim($item);
            $item = trim($item, '. ');
            if (! empty($item)) {
                $formatted .= '    '.$item."\n";
            }
        }

        return rtrim($formatted);
    }

    return ' '.$details;
}

// Read CSV file
$file = fopen('input.csv', 'r');
if (! $file) {
    exit('Unable to open input.csv');
}

// Skip header row
$header = fgetcsv($file);

// Define status priority for sorting
$statusPriority = [
    'Breaking' => 1,
    'Dependency' => 2,
    'Medium' => 3,
    'Minor' => 4,
    'Other' => 5,
];

// Initialize data structure by type and status
$data = [];

// Parse CSV data
while (($row = fgetcsv($file)) !== false) {
    if (count($row) < 5) {
        continue;
    } // Skip invalid rows

    [$type, $status, $description, $pr, $details] = $row;

    if (! isset($data[$type])) {
        $data[$type] = [];
    }

    // If status is null, don't include a status prefix
    $statusKey = ($status !== 'null') ? $status : '';

    if (! isset($data[$type][$statusKey])) {
        $data[$type][$statusKey] = [];
    }

    $data[$type][$statusKey][] = [
        'description' => $description,
        'pr' => $pr,
        'details' => $details,
    ];
}
fclose($file);

// Sort entries within each category alphabetically
foreach ($data as $type => &$statuses) {
    foreach ($statuses as &$items) {
        usort($items, function ($a, $b) {
            return strcmp($a['description'], $b['description']);
        });
    }
}

// Generate Markdown output
$output = '';

// Process each category
foreach ($data as $type => $statuses) {
    $output .= "### {$type}\n\n";

    // Get all status keys
    $statusKeys = array_keys($statuses);

    // Sort status keys by priority
    usort($statusKeys, function ($a, $b) use ($statusPriority) {
        $aPriority = $statusPriority[$a] ?? 999;
        $bPriority = $statusPriority[$b] ?? 999;

        return $aPriority - $bPriority;
    });

    // Process all items with their status prefix
    foreach ($statusKeys as $status) {
        foreach ($statuses[$status] as $item) {
            $details = formatDetails($item['details']);
            $statusPrefix = ! empty($status) ? "**{$status}:** " : '';
            $prSuffix = ! empty($item['pr']) ? " in [#{$item['pr']}](https://github.com/hydephp/develop/pull/{$item['pr']})" : '';

            $description = trim($item['description'], '. ');
            $output .= "- {$statusPrefix}$description{$prSuffix}{$details}\n";
        }
    }

    $output .= "\n";
}

// Write to output file
file_put_contents('output.md', trim($output)."\n");
echo "Successfully created output.md\n";
