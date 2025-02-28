CREATE TABLE IF NOT EXISTS patient
(
    _id   int(10) unsigned AUTO_INCREMENT NOT NULL primary key,
    pn    varchar(11) default null,
    first varchar(15) default null,
    last  varchar(25) default null,
    dob   date        default null
);

create procedure if not exists check_dob(new_dob date)
begin
    IF new_dob > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
end;

CREATE TRIGGER if not exists check_dob_before_insert
    BEFORE INSERT
    ON patient
    FOR EACH ROW
BEGIN
    CALL check_dob(NEW.dob);
END;

CREATE TRIGGER if not exists check_dob_before_update
    BEFORE UPDATE
    ON patient
    FOR EACH ROW
BEGIN
    CALL check_dob(NEW.dob);
END;

CREATE TABLE IF NOT EXISTS insurance
(
    _id        int(10) unsigned AUTO_INCREMENT NOT NULL primary key,
    patient_id int(10) unsigned                NOT NULL,
    iname      varchar(40) default null,
    from_date  date        default null,
    to_date    date        default null,
    FOREIGN KEY (patient_id) REFERENCES patient (_id),
    constraint chk_validity check (from_date <= to_date)
);

insert into patient (pn, first, last, dob)
values ('000000004', 'Olivia', 'Johnson', '1990-05-07');
insert into patient (pn, first, last, dob)
values ('000000005', 'Eva-Anna', 'Smith', '1993-12-12');
insert into patient (pn, first, last, dob)
values ('000000006', 'Emily', 'Miller', '2005-08-23');
insert into patient (pn, first, last, dob)
values ('000000001', 'John', 'Doe', '1970-03-03');
insert into patient (pn, first, last, dob)
values ('000000002', 'John-Paul', 'Smith', '1971-02-05');
insert into patient (pn, first, last, dob)
values ('000000003', 'Donald', 'Brown', '1984-04-02');


insert into insurance (patient_id, iname, from_date, to_date)
values (1, 'Blue Shield', '2009-01-01', '2010-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (1, 'UnitedHealth', '2005-01-01', '2006-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (2, 'Medicaid', '2010-01-01', '2011-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (2, 'Blue Cross', '2012-01-01', '2013-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (3, 'Humana', '2016-01-01', '2017-01-01');
insert into insurance (patient_id, iname, from_date)
values (3, 'Kaiser Permanente', '2024-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (4, 'Medicaid', '2015-01-01', '2016-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (4, 'UnitedHealth', '2012-01-01', '2013-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (5, 'Blue Cross', '2022-01-01', '2022-06-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (5, 'Medicaid', '2024-01-01', '2025-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (6, 'Medicare', '2015-01-01', '2016-01-01');
insert into insurance (patient_id, iname, from_date, to_date)
values (6, 'Blue Shield', '2017-01-01', '2018-01-01');
