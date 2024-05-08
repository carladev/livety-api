CREATE SCHEMA LIV;

CREATE TABLE
  LIV.users (
    userId INT PRIMARY KEY AUTO_INCREMENT,
    userName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    createDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );

CREATE TABLE
  LIV.habitFrequencies (
    frequencyId VARCHAR(20) PRIMARY KEY,
    frequencyName VARCHAR(255) NOT NULL
  );

CREATE TABLE
  LIV.userHabits (
    userHabitId INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    frequencyId VARCHAR(20),
    title VARCHAR(255) NOT NULL,
    color VARCHAR(255) NOT NULL,
    icon VARCHAR(255)
  );

INSERT INTO
  LIV.habitFrequencies (frequencyId, frequencyName)
VALUES
  ('D', 'Diary'),
  ('W', 'Weekly');

SELECT
  *
FROM
  LIV.userHabits;

INSERT INTO
  LIV.userHabits (userId, frequencyId, title, color, icon)
VALUES
  (1, 'W', 'Estudiar', '#95D6D0', null);