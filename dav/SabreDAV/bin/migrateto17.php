<?php

echo "SabreDAV migrate script for version 1.7\n";

if ($argc<2) {

    echo <<<HELLO

This script help you migrate from a pre-1.7 database to 1.7 and later\n
It is important to note, that this script only touches the 'calendarobjects'
table.

If you do not have this table, or don't use the default PDO CalDAV backend
it's pointless to run this script.

Keep in mind that some processing will be done on every single record of this
table and in addition, ALTER TABLE commands will be executed.
If you have a large calendarobjects table, this may mean that this process
takes a while.

Usage:

{$argv[0]} [pdo-dsn] [username] [password]

For example:

{$argv[0]} mysql:host=localhost;dbname=sabredav root password
{$argv[0]} sqlite:data/sabredav.db

HELLO;

    exit();

}

if (file_exists(__DIR__ . '/../lib/Sabre/VObject/includes.php')) {
    include __DIR__ . '/../lib/Sabre/VObject/includes.php';
} else {
    // If, for some reason VObject was not found in the vicinity,
    // we'll try to grab it from the default path.
    require 'Sabre/VObject/includes.php';
}

$dsn = $argv[1];
$user = isset($argv[2])?$argv[2]:null;
$pass = isset($argv[3])?$argv[3]:null;

echo "Connecting to database: " . $dsn . "\n";

$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "Validating existing table layout\n";

// The only cross-db way to do this, is to just fetch a single record.
$row = $pdo->query("SELECT * FROM calendarobjects LIMIT 1")->fetch();

if (!$row) {
    echo "Error: This database did not have any records in the calendarobjects table, you should just recreate the table.\n";
    exit(-1);
}

$requiredFields = array(
    'id', 
    'calendardata',
    'uri',
    'calendarid', 
    'lastmodified',
);

foreach($requiredFields as $requiredField) {
    if (!array_key_exists($requiredField,$row)) {
        echo "Error: The current 'calendarobjects' table was missing a field we expected to exist.\n";
        echo "For safety reasons, this process is stopped.\n";
        exit(-1);
    }
}

$fields17 = array(
    'etag',
    'size',
    'componenttype',
    'firstoccurence',
    'lastoccurence',
);

$found = 0;
foreach($fields17 as $field) {
    if (array_key_exists($field, $row)) {
        $found++;
    }
}

if ($found === 0) {
    echo "The database had the 1.6 schema. Table will now be altered.\n";
    echo "This may take some time for large tables\n";
    $pdo->exec(<<<SQL
ALTER TABLE calendarobjects 
ADD etag VARCHAR(32),
ADD size INT(11) UNSIGNED,
ADD componenttype VARCHAR(8),
ADD firstoccurence INT(11) UNSIGNED,
ADD lastoccurence INT(11) UNSIGNED
SQL
);
    echo "Database schema upgraded.\n";

} elseif ($found === 5) {

    echo "Database already had the 1.7 schema\n";

} else {

    echo "The database had $found out of 5 from the changes for 1.7. This is scary and unusual, so we have to abort.\n";
    echo "You can manually try to upgrade the schema, and then run this script again.\n";
    exit(-1);

}

echo "Now, we need to parse every record and pull out some information.\n";

$result = $pdo->query('SELECT id, calendardata FROM calendarobjects');
$stmt = $pdo->prepare('UPDATE calendarobjects SET etag = ?, size = ?, componenttype = ?, firstoccurence = ?, lastoccurence = ? WHERE id = ?'); 

echo "Total records found: " . $result->rowCount() . "\n";
$done = 0;
$total = $result->rowCount();
while($row = $result->fetch()) {

    try {
        $newData = getDenormalizedData($row['calendardata']);
    } catch (Exception $e) {
        echo "===\nException caught will trying to parser calendarobject.\n";
        echo "Error message: " . $e->getMessage() . "\n";
        echo "Record id: " . $row['id'] . "\n";
        echo "This record is ignored, you should inspect it to see if there's anything wrong.\n===\n";
        continue;
    }
    $stmt->execute(array(
        $newData['etag'],
        $newData['size'],
        $newData['componentType'],
        $newData['firstOccurence'],
        $newData['lastOccurence'],
        $row['id'],
    ));
    $done++;

    if ($done % 500 === 0) {
        echo "Completed: $done / $total\n";
    } 
}

echo "Process completed!\n";

/**
 * Parses some information from calendar objects, used for optimized
 * calendar-queries.
 *
 * Blantently copied from Sabre_CalDAV_Backend_PDO
 *
 * Returns an array with the following keys:
 *   * etag
 *   * size
 *   * componentType
 *   * firstOccurence
 *   * lastOccurence
 *
 * @param string $calendarData
 * @return array
 */
function getDenormalizedData($calendarData) {

    $vObject = Sabre_VObject_Reader::read($calendarData);
    $componentType = null;
    $component = null;
    $firstOccurence = null;
    $lastOccurence = null;
    foreach($vObject->getComponents() as $component) {
        if ($component->name!=='VTIMEZONE') {
            $componentType = $component->name;
            break;
        }
    }
    if (!$componentType) {
        throw new Sabre_DAV_Exception_BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
    }
    if ($componentType === 'VEVENT') {
        $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
        // Finding the last occurence is a bit harder
        if (!isset($component->RRULE)) {
            if (isset($component->DTEND)) {
                $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
            } elseif (isset($component->DURATION)) {
                $endDate = clone $component->DTSTART->getDateTime();
                $endDate->add(Sabre_VObject_DateTimeParser::parse($component->DURATION->value));
                $lastOccurence = $endDate->getTimeStamp();
            } elseif ($component->DTSTART->getDateType()===Sabre_VObject_Property_DateTime::DATE) {
                $endDate = clone $component->DTSTART->getDateTime();
                $endDate->modify('+1 day');
                $lastOccurence = $endDate->getTimeStamp();
            } else {
                $lastOccurence = $firstOccurence;
            }
        } else {
            $it = new Sabre_VObject_RecurrenceIterator($vObject, (string)$component->UID);
            $maxDate = new DateTime(self::MAX_DATE);
            if ($it->isInfinite()) {
                $lastOccurence = $maxDate->getTimeStamp();
            } else {
                $end = $it->getDtEnd();
                while($it->valid() && $end < $maxDate) {
                    $end = $it->getDtEnd();
                    $it->next();

                }
                $lastOccurence = $end->getTimeStamp();
            }

        }
    }

    return array(
        'etag' => md5($calendarData),
        'size' => strlen($calendarData),
        'componentType' => $componentType,
        'firstOccurence' => $firstOccurence,
        'lastOccurence'  => $lastOccurence,
    );

}
