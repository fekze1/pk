DELIMITER $$

CREATE TRIGGER after_passport_insert
AFTER INSERT ON Passport
FOR EACH ROW
BEGIN
    -- Обновляем поле passport_id в таблице Applicant
    UPDATE Applicant
    SET passport_id = NEW.passport_id
    WHERE applicant_id = (SELECT applicant_id FROM Applicant WHERE passport_id IS NULL LIMIT 1);
END$$

DELIMITER ;