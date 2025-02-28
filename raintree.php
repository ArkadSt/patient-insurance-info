<?php
require_once("Patient.php");
require_once("utils.php");

/**
 * 1. Exercise. Print insurance info for patients,
 * @param PDO $conn database connection
 * @return void
 */
function printPatientInsuranceInfo(PDO $conn): void
{
    try {
        $stmt = $conn->prepare("select pn, last, first, insurance.iname, insurance.from_date, insurance.to_date from patient
        inner join insurance
        on patient._id = insurance.patient_id
        order by from_date, last asc");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                // if property is null replace with "NULL" for better representation.
                // If not null and is from_date or to_date, convert to US short form date format.
                if ($value == null) {
                    $row[$key] = "NULL";
                } elseif ($key == "from_date" || $key == "to_date") {
                    $row[$key] = formatDate($value);
                }
            }
            echo implode(", ", $row) . "\n";
        }
    } catch (PDOException $e) {
        printFetchingDataError($e);
    }

}

/**
 * 2. Exercise.- Prints letter statistics
 * @param PDO $conn database connection
 * @return void
 */
function printLetterStatistics(PDO $conn): void
{
    try {
        $letter_counter = array();

        $stmt = $conn->prepare("select first, last from patient");
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_letters = 0;
        foreach ($patients as $patient) {
            // transfers all letters from first and last names to an array in uppercase, removes all special characters.
            $letters = str_split(preg_replace('/[^A-Z]/', '', strtoupper($patient['first'] . $patient['last'])));
            foreach ($letters as $letter) {
                $total_letters += 1;
                $letter_counter[$letter] = ($letter_counter[$letter] ?? 0) + 1;
            }
        }
        ksort($letter_counter);
        foreach ($letter_counter as $key => $value) {
            // outputs letter, count, percentage from total
            echo "$key\t$value\t" . round(($value / $total_letters) * 100, 2) . "%\n";
        }
    } catch (PDOException $e) {
        printFetchingDataError($e);
    }
}

/**
 * 3. Exercise. Tests Patient and Insurance classes,
 * outputs insurance records' validity information at the moment of script execution
 * @param PDO $conn database connection
 * @return void
 */
function testClasses(PDO $conn): void
{
    try {
        $stmt = $conn->prepare("select pn from patient");
        $stmt->execute();
        $patients_numbers = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'pn');

        sort($patients_numbers);
        foreach ($patients_numbers as $patient_number) {
            $patient = new Patient($conn, $patient_number);
            $patient->printPatientInsuranceValidityInfo(formatDate(null));
        }
    } catch (PDOException $e) {
        printFetchingDataError($e);
    }

}

/**
 * Main loop which provides prompt to the user.
 * @param PDO $conn database connection
 * @return void
 */
function readUserInput(PDO $conn): void
{
    switch (trim(readline('(m for menu) >>> '))) {
        case '1':
            printPatientInsuranceInfo($conn);
            break;
        case '2':
            printLetterStatistics($conn);
            break;
        case '3':
            testClasses($conn);
            break;
        case 'm':
            showMenu();
            break;
        case 'q':
            echo "Goodbye!\n";
            return;
        default:
            echo "Invalid input. Please try again\n";
    }
    readUserInput($conn);
}

/**
 * Shows menu
 * @return void
 */
function showMenu(): void
{
    echo "Please choose what you want to do: \n";
    echo "(1) Print database extract\n";
    echo "(2) Print letter occurrence statistics\n";
    echo "(3) Test classes Patient and Insurance)\n";
    echo "(m) Show this menu\n";
    echo "(q) Quit\n";
}

/**
 * Function responsible for establishing a connection to the database. Uses credentials specified in the database.ini file.
 * @return PDO|null returns PDO instance in case of success, null otherwise
 */
function connectToDatabase(): ?PDO
{
    try {
        $db_credentials = parse_ini_file("database.ini");
        if (!$db_credentials) {
            echo "Unable to parse database.ini file\n";
            return null;
        }
        $conn = new PDO($db_credentials["dsn"], $db_credentials["username"], $db_credentials["password"]);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage() . "\n";
        return null;
    }
}

try {
    $conn = connectToDatabase();
    if ($conn) {
        echo "Successfully connected to the database!\n";
        echo "Hello!\n\n";
        showMenu();
        readUserInput($conn);
        $conn = null;
    }
} catch (Exception $e) {
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
}





