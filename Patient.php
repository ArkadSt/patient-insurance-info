<?php
require_once("PatientRecord.php");
require_once("Insurance.php");
require_once("utils.php");

class Patient implements PatientRecord
{

    private int $_id;
    private ?string $pn;
    private ?string $first;
    private ?string $last;
    private ?string $dob;
    private array $insurances;

    /**
     * Loads patient data specified by patient number (pn)
     * @param PDO $conn database connection
     * @param string $pn patient number
     * @return void
     */
    private function loadPatientData(PDO $conn, string $pn): void
    {
        $stmt = $conn->prepare("select * from patient where pn = :pn");
        $stmt->bindParam(":pn", $pn);
        $stmt->execute();
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($patient) {
            $this->_id = $patient["_id"];
            $this->pn = $patient["pn"];
            $this->first = $patient["first"];
            $this->last = $patient["last"];
            $this->dob = $patient["dob"];
        } else {
            throw new PDOException("Error initializing Patient object. No patient with pn=$pn");
        }
    }

    /**
     * Loads patient's insurances
     * @param PDO $conn database connection
     * @return void
     */
    private function loadPatientInsurances(PDO $conn): void
    {
        $stmt = $conn->prepare("select _id from insurance where patient_id = :_id");
        $stmt->bindParam(":_id", $this->_id);
        $stmt->execute();
        $insurance_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), "_id");
        foreach ($insurance_ids as $insurance_id) {
            $this->insurances[] = new Insurance($conn, $insurance_id);
        }
    }

    /**
     * @param PDO $conn database connection
     * @param string $pn patient number
     */
    function __construct(PDO $conn, string $pn)
    {
        $this->insurances = array();
        try {
            $this->loadPatientData($conn, $pn);
            $this->loadPatientInsurances($conn);
        } catch (PDOException $e) {
            printFetchingDataError($e);
        }

    }


    /**
     * @return int patient _id
     */
    public function get_ID(): int
    {
        return $this->_id;
    }

    /**
     * @return string patient number
     */
    public function getPatientNumber(): string
    {
        return $this->pn;
    }

    /**
     * @return string patient's full name in the form of "John Doe"
     */
    public function getPatientFullName(): string
    {
        return $this->first . ' ' . $this->last;
    }

    /**
     * @return array array of Insurance objects
     */
    public function getInsurances(): array
    {
        return $this->insurances;
    }

    /**
     * Prints patient insurance info together with whether it is valid on a given date
     * @param string $date a date in US short date format
     * @return void
     */
    public function printPatientInsuranceValidityInfo(string $date): void
    {
        try{
            /** @var Insurance $insurance */
            foreach ($this->getInsurances() as $insurance) {
                echo $this->getPatientNumber() . ", " . $this->getPatientFullName() . ", " . $insurance->getIName() . ", " . (($insurance->isValid($date)) ? "Yes" : "No") . "\n";
            }
        } catch (Exception $e) {
            echo "Error checking insurance validity: " . $e->getMessage() . "\n";
        }
    }
}