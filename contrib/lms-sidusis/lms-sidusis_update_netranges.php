#!/usr/bin/env php
<?php
/*
 *  lms-sidusis_update_netranges.php
 *  for LMS 27.52+
 *
 *  (C) 2022 Michał Dąbrowski, michal@euro-net.pl
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *
 *  $Id$
 */



### user variables
## node names array left as an example. Change to names existing in your DB with desired technologies stack(s)
## 'SW-%' - will look for names BEGINNING WITH 'SW-', to look for names CONTAINING a substring use: '%SW-%' [an example, two '%' are important here]
$node_names = array(
        'SW-%' => array(
            1 => array('linktype' => 0, 'linktechnology' => 8, 'downlink' => 1000 , 'uplink' => 1000, 'type' => 1, 'services' => 2),
            2 => array('linktype' => 0, 'linktechnology' => 7, 'downlink' => 100 , 'uplink' => 100, 'type' => 1, 'services' => 2),
        ),
        'GPON-%' => array(
            1 => array('linktype' => 2, 'linktechnology' => 209, 'downlink' => 1000 , 'uplink' => 500, 'type' => 1, 'services' => 2),
        ),
        'ZTE-%' => array(
            1 => array('linktype' => 2, 'linktechnology' => 209, 'downlink' => 600 , 'uplink' => 300, 'type' => 1, 'services' => 2),
        ),
);

# change to true after updating above $node_names array:
#$i_updated_my_vars = false;
$i_updated_my_vars = true;

# link types:
/*
przewodowy: define('LINKTYPE_WIRE', 0);
bezprzewodowy: define('LINKTYPE_WIRELESS', 1);
światłowodowy: define('LINKTYPE_FIBER', 2);

# link technologies:
dla typu >przewodowy<:
        1 => 'ADSL',
        2 => 'ADSL2',
        3 => 'ADSL2+',
        4 => 'VDSL',
        5 => 'VDSL2',
        6 => '10 Mb/s Ethernet',
        7 => '100 Mb/s Fast Ethernet',
        8 => '1 Gigabit Ethernet',
        9 => '10 Gigabit Ethernet',
        11 => 'SDH/PDH',
        13 => 'VDSL2(vectoring)',
        14 => 'G.Fast',
        15 => '2,5 Gigabit Ethernet',
        16 => '5 Gigabit Ethernet',
        17 => 'MoCA',
        18 => 'EoC',
        50 => '(EURO)DOCSIS 1.x',
        51 => '(EURO)DOCSIS 2.x',
        52 => '(EURO)DOCSIS 3.x',

dla typu >BEZprzewodowy<:
        112 => 'LTE',
        117 => 'LTE-A',
        118 => 'LTE-Pro',
        119 => 'NR SA',
        120 => 'NR NSA',

dla typu >światłowodowy<:
        203 => '10 Mb/s Ethernet',
        204 => '100 Mb/s Fast Ethernet',
        205 => '1 Gigabit Ethernet',
        206 => '10 Gigabit Ethernet',
        207 => '100 Gigabit Ethernet',
        208 => 'EPON',
        209 => 'GPON',
        212 => 'SDH/PDH',
        213 => '2,5 Gigabit Ethernet',
        214 => '5 Gigabit Ethernet',
        215 => '25 Gigabit Ethernet',
        216 => '10G-EPON',
        217 => 'CWDM',
        218 => 'DWDM',
        219 => 'NGPON1 (XGPON)',
        220 => 'NGPON2 (XGPON)',
        221 => 'XGSPON',
        222 => '25G PON',
        223 => 'MoCA',
        224 => 'EoC',
        250 => '(EURO)DOCSIS 1.x',
        251 => '(EURO)DOCSIS 2.x',
        252 => '(EURO)DOCSIS 3.x',

# link speeds:
LINKSPEEDS:
    100       => 100 Mbit/s,
    1000      => 1 Gbit/s,
    etc.

# type:
    real => 1,
    theoretical => 2,

# services:
    bulk => 1,
    retail => 2,
*/

### end of user variables

# PLEASE DO NOT MODIFY ANYTHING BELOW THIS LINE UNLESS YOU KNOW
# *EXACTLY* WHAT ARE YOU DOING!!!
# *******************************************************************

ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$parameters = array(
    'config-file:' => 'C:',
    'help' => 'h',
    'initial_run' => 'i',
    'update' => 'u',
    'add_theoretical_ranges' => 't',
    'minimal_logging' => 'm',
);

$long_to_shorts = array();
foreach ($parameters as $long => $short) {
    $long = str_replace(':', '', $long);
    if (isset($short)) {
        $short = str_replace(':', '', $short);
    }
    $long_to_shorts[$long] = $short;
}

$options = getopt(
    implode(
        '',
        array_filter(
            array_values($parameters),
            function ($value) {
                return isset($value);
            }
        )
    ),
    array_keys($parameters)
);

foreach (array_flip(array_filter($long_to_shorts, function ($value) {
    return isset($value);
})) as $short => $long) {
    if (array_key_exists($short, $options)) {
        $options[$long] = $options[$short];
        unset($options[$short]);
    }
}

if (array_key_exists('help', $options)) {
    print <<<EOF
lms-sidusis_update_netranges.php
(C) 2022 Michał Dąbrowski, michal@euro-net.pl

-C, --config-file=/etc/lms/lms.ini      alternate config file (default: /etc/lms/lms.ini).
-h, --help                              print this help and exit.

-i, --initial_run                       BY DEFAULT THIS SCRIPT RUNS IN TEST MODE (no data is written to DB)!
                                        Use this parameter to DELETE EVERYTHING from netranges table and populate it again with user configured data.
                                        
-u, --update                            BY DEFAULT THIS SCRIPT RUNS IN TEST MODE (no data is written to DB)!
                                        Use this parameter to UPDATE existing netranges table (performs checks if address already exists in DB). Adds only nonexisting ones.
                                        WILL also check if existing DB table data is accurate - i.e. if address was removed or should be changed it'll do it) - NOT IMPLEMENTED YET!
                                        
-t, --add_theoretical_ranges            NOT IMPLEMENTED YET.
                                        Add theoretical ranges based on already populated netranges table: if street exists in netranges as a >REAL< netrange with 
                                        at least one building number add all remaining numbers as theoretical ranges.                                             

-m, --minimal_logging                   Does not produce diff logs in 'get_building_ids_for_node_type' function.
EOF;
    exit(0);
}


if (array_key_exists('config-file', $options)) {
    $CONFIG_FILE = $options['config-file'];
} else {
    $CONFIG_FILE = DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'lms' . DIRECTORY_SEPARATOR . 'lms.ini';
}
echo "Using file ".$CONFIG_FILE." as config." . PHP_EOL;

if (!is_readable($CONFIG_FILE)) {
    die('Unable to read configuration file ['.$CONFIG_FILE.']!');
}

define('CONFIG_FILE', $CONFIG_FILE);

$CONFIG = (array) parse_ini_file($CONFIG_FILE, true);

// Check for configuration vars and set default values
$CONFIG['directories']['sys_dir'] = (!isset($CONFIG['directories']['sys_dir']) ? getcwd() : $CONFIG['directories']['sys_dir']);
$dbtype = $CONFIG['database']['type'];

define('SYS_DIR', $CONFIG['directories']['sys_dir']);

// Load autoloader
$composer_autoload_path = SYS_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($composer_autoload_path)) {
    require_once $composer_autoload_path;
} else {
    die("Composer autoload not found. Run 'composer install' command from LMS directory and try again. More information at https://getcomposer.org/" . PHP_EOL);
}

# check db type
if ($dbtype == 'postgres') {
    echo "Found supported DB type.\n\n";
} else {
    die("Fatal error: unsupported DB type! Only postgresql DB is supported! Exiting...\n" . PHP_EOL);
}

# check user vars
if (!$i_updated_my_vars) {
    die("Fatal error: User variables not set! Check readme file! Exiting...\n" . PHP_EOL);
}

# without following declarations phpstorm gives weak warning "variable is probably undefined" for code using said variables (these are script parameters vars)
# php still handles them as if they were normally declared. Adding declarations here just to be safe...
# I'm obviously NOT a php guru :)
$initial_run = false;
$update = false;
$add_theoretical_ranges = false;
$minimal_logging = false;

# assign parameters
if (array_key_exists('initial_run', $options)) {
    $initial_run = array_key_exists('initial_run', $options);
}
if (array_key_exists('update', $options)) {
    $update = array_key_exists('update', $options);
}
if (array_key_exists('add_theoretical_ranges', $options)) {
    $add_theoretical_ranges = array_key_exists('add_theoretical_ranges', $options);
}
if (array_key_exists('minimal_logging', $options)) {
    $minimal_logging = array_key_exists('minimal_logging', $options);
}

# check for incorrectly used parameters
if ($initial_run && $update) {
    echo "Error! >initial_run< and >update< parameters are mutually exclusive. Choose one! Exiting...\n\n";
    exit(0);
}

// Init database
$DB = null;

try {
    $DB = LMSDB::getInstance();
} catch (Exception $ex) {
    trigger_error($ex->getMessage(), E_USER_WARNING);
    // can't work without database
    die("Fatal error: cannot connect to database!" . PHP_EOL);
}

$SYSLOG = SYSLOG::getInstance();

// Initialize Session, Auth and LMS classes

$AUTH = null;
$LMS = new LMS($DB, $AUTH, $SYSLOG);

$candidates_array = array();
##### MAIN
# [did you also start learning programming with the C language? MAIN rules! xD]
if (!$add_theoretical_ranges) {
    # no theoretical ranges parameter means we get to analyze LMS DB for a given device types
    foreach ($node_names as $name => $values) {
        # create building id's array for selected node name
        echo "Getting building id's for node names: " . $name . "\n";
        $current_building_ids_array = get_building_ids_for_node_type($name);
        echo "Returned to MAIN. Got " . count($current_building_ids_array) . " building id's from >>" . $name . "<< node group\n";

        foreach ($values as $value) {
            # update candidates array with user selected technologies for currently analyzed node name
            echo "Updating candidates array for node name " . $name . " and technologies set in user vars...(I'm too lazy to list them :])\n";
            foreach ($current_building_ids_array as $building_id) {
                $candidates_array[] = array('building_id' => $building_id, 'linktype' => $value['linktype'], 'linktechnology' => $value['linktechnology'],
                    'downlink' => $value['downlink'], 'uplink' => $value['uplink'], 'type' => $value['type'], 'services' => $value['services']);
            }
        }
        echo "Done with " . $name . ", proceeding...\n\n";
    }
    # perform additional duplicates check on finished candidates array and eliminate values with the same linktype, linktechnology, type and services,
    # but lower speeds (since we have to report highest possible speed for a given technology "stack"")
    # because I already have a good and tested duplicates check function - I'll use it here and then compare items from
    # resulting duplicates array with corresponding unique array entries

    echo "Checking for duplicates in candidates array...\n";
    list($unique_addresses, $duplicates/*, $unique_keys */) = unique_multidim_array($candidates_array, 'building_id');
    if (!empty($duplicates)) {
        $LOGFILE_CANDIDATE_DUPES = fopen("candidates_array_duplicates.log", "w"); # modes: w - overwrite existing, a - append to existing
        $data = "VALID duplicate devices in candidates (same 'technology stack', different speed): \n";
        fwrite($LOGFILE_CANDIDATE_DUPES, $data);

        $dupes_counter = 0;
        foreach ($duplicates as $dup_val) {
            # find key of an item that was left in uniqes array [it "generated" our duplicate]. IT MUST EXIST, if we can't find it - it's a bug!
            $uniq_key = array_search($dup_val['building_id'], array_column($unique_addresses, 'building_id'));
            if ($uniq_key !== false) { # php type casting... without triple === it interprets int 0 [possible here] as false :)
                if ($unique_addresses[$uniq_key]['linktype'] === $dup_val['linktype'] AND $unique_addresses[$uniq_key]['linktechnology'] === $dup_val['linktechnology'] AND
                    $unique_addresses[$uniq_key]['type'] === $dup_val['type'] AND $unique_addresses[$uniq_key]['services'] === $dup_val['services']) {
                    # looking for "tech stack" exact match [for a single building id], but with different speeds
                    $dupes_counter ++;
                    $echo_dupes = $dup_val['building_id'] . ", " . $dup_val['linktype'] . ", " . $dup_val['linktechnology'] .
                        ", " . $dup_val['downlink'] . ", " . $dup_val['uplink'] . ", " . $dup_val['type'] . ", " . $dup_val['services'];
                    echo "Duplicate BUILDING ID found in candidates array with same technology stack but different speed values. Duplicates:\n" . $echo_dupes . "; Left in candidates: \n";
                    $echo_candidates = $unique_addresses[$uniq_key]['building_id'] . ", " . $unique_addresses[$uniq_key]['linktype'] . ", " . $unique_addresses[$uniq_key]['linktechnology'] .
                        ", " . $unique_addresses[$uniq_key]['downlink'] . ", " . $unique_addresses[$uniq_key]['uplink'] . ", " . $unique_addresses[$uniq_key]['type'] . ", " .
                        $unique_addresses[$uniq_key]['services'];
                    echo $echo_candidates . "\n";
                    if ($unique_addresses[$uniq_key]['downlink'] < $dup_val['downlink']) {
                        $unique_addresses[$uniq_key]['downlink'] = $dup_val['downlink'];
                    }
                    if ($unique_addresses[$uniq_key]['uplink'] < $dup_val['uplink']) {
                        $unique_addresses[$uniq_key]['uplink'] = $dup_val['uplink'];
                    }
                    $data = "Got a valid duplicate for building id: " . $unique_addresses[$uniq_key]['building_id'] . ". Values:\n" . $echo_dupes . "\n" . $echo_candidates .
                        "\nSelected values (to be added to DB):\n" . $echo_candidates . "\n";
                    fwrite($LOGFILE_CANDIDATE_DUPES, $data);
                    echo "Corrected speeds. New values in candidates array:\n" . $echo_candidates . "\n\n";
                } else {
                    # "tech stack" exact match [for a single building id] NOT FOUND - it's 'false duplicate', moving it back to candidates.
                    # Since we used our dupes check function that compares only 'building_id' key - there can be a lot of those.
                    # Not an error, function could be done better, if I just had the time... :)
                    $unique_addresses[] = array('building_id' => $dup_val['building_id'], 'linktype' => $dup_val['linktype'], 'linktechnology' => $dup_val['linktechnology'],
                        'downlink' => $dup_val['downlink'], 'uplink' => $dup_val['uplink'], 'type' => $dup_val['type'], 'services' => $dup_val['linktype']);
                }
            } else {
                # that's serious problem here! Report it to me!
                echo "Error! Found duplicate(s) in candidates array, but can't seem to find correct key for it! It's important error that needs to be debugged!!!\n";
                var_dump($dup_val);
            }
        }
        $candidates_array = $unique_addresses;
        fclose($LOGFILE_CANDIDATE_DUPES);
        echo "A total of " . $dupes_counter . " valid duplicates were found.\n";
    } else {
        echo "No duplicates found...\n";
    }
    echo "\nCANDIDATES ARRAY created and checked. Proceeding...\n\n";

    if ($initial_run) {
        echo ">initial_run< parameter selected. Cleaning netranges table...";
        $DB->Execute("TRUNCATE TABLE netranges");
        $DB->Execute("ALTER SEQUENCE netranges_id_seq RESTART");
        echo "done!\nPopulating table with new data from candidates array. Expecting " . count($candidates_array) . " new rows...\n";
        foreach ($candidates_array as $value) {
            $DB->Execute("INSERT INTO netranges (buildingid, linktype, linktechnology, downlink, uplink, type, services)
		VALUES (?, ?, ?, ?, ?, ?, ?)", array_values($value));
        }
        echo "...done!\nExiting...\n\n";
    } elseif ($update) {
        echo ">update< parameter selected. Preparing to update data...\n";
        # the idea here is to compare every key in candidates array with netranges table, with a few conditions:
        # 1. we have to update all of the technologies already present in DB (for which we used '-i' parameter)
        # 2. we can add a new tech, under the condition in #1.
        # 3. new buildings associated with a given technology will be added to DB
        # 4. PLANNED (NOT implemented): removing/modifying buildings if corresponding device was removed/technology changed

        $counter = 0;
        foreach ($candidates_array as $item) {
            $args = array('building_id' => $item['building_id'], 'linktype' => $item['linktype'], 'linktechnology' => $item['linktechnology'], 'downlink' => $item['downlink'],
                'uplink' => $item['uplink'], 'type' => $item['type'], 'services' => $item['services']);
            if (!$DB->GetOne("SELECT 1 FROM netranges WHERE buildingid = ? AND linktype = ? AND linktechnology = ? AND downlink = ? AND uplink = ? AND
                                                                                        type = ? AND services = ? LIMIT 1", array_values($args))) {
                $counter++;
                $DB->Execute("INSERT INTO netranges (buildingid, linktype, linktechnology, downlink, uplink, type, services) VALUES (?, ?, ?, ?, ?, ?, ?)", array_values($args));
                echo $counter . ". Added a row to DB with following values: " . $item['building_id'] . ", " . $item['linktype'] . ", " . $item['linktechnology'] . ", " .
                    $item['downlink'] . ", " . $item['uplink'] . ", " . $item['type'] . ", " . $item['services'] . "\n";
            }
        }
        echo "Update done, added " . $counter . " rows! Exiting...\n";
    } else {
        echo "Neither of the main parameters was selected. Try running script with '-h' parameter. No DB changes has been made. Exiting...\n";
        exit(0);
    }
} else {
    # theoretical ranges will be handled here. They'll require only existing netranges table check and comparison with location_buildings/location_streets
    echo "Theoretical ranges not yet implemented. Exiting...\n";
    exit(0);
}
##### end of MAIN

function get_building_ids_for_node_type($node_name) : array {
    global $DB, $minimal_logging;

    $main_query =  "SELECT n.id AS nodes_id, n.name AS nodes_name,
                    coalesce(n.address_id, ndev.address_id),
                    a.city AS addr_city, a.city_id AS addr_city_id, a.street AS addr_street, a.street_id AS addr_street_id, a.house AS addr_house, lb.id AS building_id
                    FROM nodes n, netdevices ndev, addresses a, location_buildings lb
                    WHERE n.netdev = ndev.id AND
                    coalesce(n.address_id, ndev.address_id) = a.id AND
                    (a.street_id = lb.street_id AND a.house = lb.building_num) AND
                    n.name ILIKE ?
                    ORDER BY n.id";

    $results = $DB->GetAll($main_query, array($node_name));

    # our two main arrays:
    list($unique_addresses, $duplicates/*, $unique_keys */) = unique_multidim_array($results, 'building_id');

    # Create extensive diff logs if run without 'm' parameter:
    if (!$minimal_logging) {
        # difference check between DB tables used in main query: 3 tables, 2 checks (between 1 and 2; 2 and 3) + extra check (stored in $diff_results_extra)

        $diff_query1 = "SELECT n.id AS nodes_id, n.name AS nodes_name, 
                        coalesce(n.address_id, ndev.address_id)
                        FROM nodes n, netdevices ndev
                        WHERE n.netdev = ndev.id AND
                        n.name ILIKE ?
                        ORDER BY n.id";

        $diff_query2 = "SELECT n.id AS nodes_id, n.name AS nodes_name, 
                        coalesce(n.address_id, ndev.address_id),
                        a.city AS addr_city, a.city_id AS addr_city_id, a.street AS addr_street, a.street_id AS addr_street_id, a.house AS addr_house
                        FROM nodes n, netdevices ndev, addresses a
                        WHERE n.netdev = ndev.id AND
                        coalesce(n.address_id, ndev.address_id) = a.id AND                            
                        n.name ILIKE ?
                        ORDER BY n.id";
        # $diff_query3 = $main_query; already stored in $main_query var

        $diff_results1 = $DB->GetAll($diff_query1, array($node_name));
        $diff_results2 = $DB->GetAll($diff_query2, array($node_name));
        #$diff_results3 = $results; already stored in $results var

        $diff1 = array_udiff(array_column($diff_results1, 'nodes_id'), array_column($diff_results2, 'nodes_id'), 'compare_ids');
        $diff2 = array_udiff(array_column($diff_results2, 'nodes_id'), array_column($results, 'nodes_id'), 'compare_ids');

        # create logs:
        $LOGFILE_UNIQ = fopen($node_name . "_1-unique_addresses.log", "w"); # modes: w - overwrite existing, a - append to existing
        $LOGFILE_DUPES = fopen($node_name . "_2-duplicate_addresses.log", "w");
        $LOGFILE_PROBLEMATIC = fopen($node_name . "_3-problematic_devices.log", "w");
        #chown($node_name . "_1-unique_addresses.log", 0);
        #chmod($node_name . "_1-unique_addresses.log", 0644);

        foreach ($unique_addresses as $val) {
            $data = "ID budynku: " . $val['building_id'] . "; Urządzenie: " . $val['nodes_name'] . ", " . $val['netdevices_name'] . "; Adres: " . $val['addr_city'] . ", " . $val['addr_street'] . " " . $val['addr_house'] . "\n";
            fwrite($LOGFILE_UNIQ, $data);
        }
        if ($duplicates) {
            foreach ($duplicates as $val) {
                $data = "ID budynku: " . $val['building_id'] . "; Urządzenie: " . $val['nodes_name'] . ", " . $val['netdevices_name'] . "; Adres: " . $val['addr_city'] . ", " . $val['addr_street'] . " " . $val['addr_house'] . "\n";
                fwrite($LOGFILE_DUPES, $data);
            }
        }

        $data = "Database differences >nr 0< - node ID's WITHOUT netdevice assigned: \n";
        fwrite($LOGFILE_PROBLEMATIC, $data);

        $diff_results_extra = $DB->GetAll("SELECT * FROM nodes WHERE name LIKE ? AND address_id IS NOT NULL AND netdev IS NULL", array($node_name));

        if ($diff_results_extra) {
            foreach ($diff_results_extra as $val) {
                $data = "node ID: " . $val['id'] . "; Urządzenie: " . $val['name'] . "\n";
                fwrite($LOGFILE_PROBLEMATIC, $data);
            }
        } else {
            $data = "No entries here...\n";
            fwrite($LOGFILE_PROBLEMATIC, $data);
        }

        $data = "\n\nDatabase differences >nr 1< - node ID's WITHOUT address [possibly because there's no netdevice assigned or no default location address added]: \n";
        fwrite($LOGFILE_PROBLEMATIC, $data);

        if ($diff1) {
            foreach ($diff1 as $val) {
                $data = $val . "\n";
                fwrite($LOGFILE_PROBLEMATIC, $data);
            }
        } else {
            $data = "No entries here...\n";
            fwrite($LOGFILE_PROBLEMATIC, $data);
        }

        $data = "\n\nDatabase differences >nr 2< - node ID's with an address BUT WITHOUT corresponding location_buildings ID\n" .
        "(most likely no such address in POLISH GOVERNMENT'S DB - which is expected, but CAN ALSO MEAN incorrect address in LMS!): \n";
        fwrite($LOGFILE_PROBLEMATIC, $data);

        if ($diff2) {
            foreach ($diff2 as $val) {
                $data = $val . "\n";
                fwrite($LOGFILE_PROBLEMATIC, $data);
            }
        } else {
            $data = "No entries here...\n";
            fwrite($LOGFILE_PROBLEMATIC, $data);
        }

        fclose($LOGFILE_UNIQ);
        fclose($LOGFILE_DUPES);
        fclose($LOGFILE_PROBLEMATIC);
    } else {
        echo "Skipping diff logs for node names: " . $node_name . " - because of 'm' parameter.\n";
    }

    # return array of location buildings id's:
    $tmp = array_column($unique_addresses, 'building_id');
    sort($tmp);
    return $tmp;
}

function compare_ids($a, $b) : int {
    # used in step-by-step differences search for extensive logging
    return strcmp($a, $b); # returns 0 if strings are equal
}

function unique_multidim_array($array, $key) : array {
    # function copied from: https://www.php.net/manual/en/function.array-unique.php [heavily modified]
    # used to check for and eliminate duplicates from selected nodes group
    $uniq_array = array();
    $dup_array = array();
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[] = $val[$key];
            $uniq_array[] = $val;
        } else {
            $dup_array[] = $val;
        }
    }
    return array($uniq_array, $dup_array/*, $key_array */);
}
