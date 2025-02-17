DELIMITER $$

CREATE TRIGGER after_certificate_insert
AFTER INSERT ON Certificate
FOR EACH ROW
BEGIN
    -- Обновляем поле certificate_id в таблице Applicant
    UPDATE Applicant
    SET certificate_id = NEW.certificate_id
    WHERE applicant_id = (SELECT applicant_id FROM Applicant WHERE certificate_id IS NULL LIMIT 1);
END$$

DELIMITER ;