<?php

require_once("PatientRecord.php");
require_once("utils.php");

class Insurance implements PatientRecord
{

    private int $_id;
    private string $pn;
    private ?string $iname;
    private ?string $from_date;
    private ?string $to_date;

    /**
     * Loads insurance data from the database specified by id.
     * @param PDO $conn database connection
     * @param int $_id insurance _id
     * @return void
     */
    function loadInsuranceData(PDO $conn, int $_id): void
    {
        $stmt = $conn->prepare("select * from insurance where _id = :_id");
        $stmt->bindParam(":_id", $_id, PDO::PARAM_INT);
        $stmt->execute();
        $insurance_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($insurance_record) {
            // an additional step to assign value to patient number, for that we have to request data from patient table
            $stmt = $conn->prepare("select pn from patient where _id = :patient_id");
            $stmt->bindParam(":patient_id", $insurance_record['patient_id'], PDO::PARAM_INT);
            $stmt->execute();
            $patient_with_pn_only = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($patient_with_pn_only) {
                $this->_id = $insurance_record['_id'];
                $this->iname = $insurance_record['iname'];
                $this->from_date = $insurance_record['from_date'];
                $this->to_date = $insurance_record['to_date'];
                $this->pn = $patient_with_pn_only['pn'];
            } else {
                throw new PDOException("Error initializing Insurance object. No patient with _id=" . $insurance_record['patient_id'] . ".");
            }
        } else {
            throw new PDOException("Error initializing Insurance object. No insurance with _id=$_id.");
        }
    }

    /**
     * @param PDO $conn database connection
     * @param int $_id insurance _id
     */
    public function __construct(PDO $conn, int $_id)
    {
        try {
            $this->loadInsuranceData($conn, $_id);
        } catch (PDOException $e) {
            printFetchingDataError($e);
        }

    }

    /**
     * @return int insurance _id
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
     * Checks validity of the insurance record on the given date
     * @param string $date date in US short date format
     * @return bool true if insurance is valid on given date
     * @throws Exception
     */
    public function isValid(string $date): bool
    {
        // convert US short date to timestamp
        $datetime = DateTime::createFromFormat('m-d-y', $date);
        if (!$datetime) {
            throw new Exception("Invalid date format given.");
        }
        $timestamp = $datetime->getTimestamp();

        // return true if the insurance record is valid at the time given.
        // If to_date is null, the insurance is valid indefinitely starting from the from_date
        return (strtotime($this->from_date) <= $timestamp && ($timestamp <= strtotime($this->to_date)) || $this->to_date == null);
    }

    /**
     * returns insurance name
     * @return string insurance name
     */
    public function getIName(): string
    {
        return $this->iname;
    }
}