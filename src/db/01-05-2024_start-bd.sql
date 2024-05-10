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
  LIV.habits (
    habitId INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    habitName VARCHAR(255) NOT NULL,
    color VARCHAR(255) NOT NULL,
    icon VARCHAR(255),
    frequencyId VARCHAR(5),
    habitGoal INT,
    habitGoalUnit VARCHAR(255),
    enabled BOOLEAN default true,
    createDate DATETIME default NOW ()
  );

CREATE TABLE
  LIV.habitsWeekDays (habitId INT, weekdayId VARCHAR(5));

CREATE TABLE
  LIV.weekDays (weekdayId VARCHAR(5), weekdayName VARCHAR(20));

CREATE TABLE
  LIV.frequencies (
    frequencyId VARCHAR(5) PRIMARY KEY,
    frequencyName VARCHAR(100) NOT NULL
  );

CREATE TABLE
  LIV.defaultColors (color VARCHAR(20));

INSERT INTO
  LIV.defaultColors (color)
VALUES
  ('#ef94c8'),
  ('#e194eb'),
  ('#eb8bad'),
  ('#eb867e'),
  ('#e8ab6d'),
  ('#ddc753'),
  ('#b3ce5e'),
  ('#7ec69d'),
  ('#6dc9bc'),
  ('#74d0bd'),
  ('#6db5dc'),
  ('#7fa7e5'),
  ('#898ce2');

INSERT INTO
  LIV.weekDays (weekdayId, weekdayName)
VALUES
  ('MON', 'Monday'),
  ('TUE', 'Tuesday'),
  ('WED', 'Wednesday'),
  ('THU', 'Thursday'),
  ('FRI', 'Friday'),
  ('SAT', 'Saturday'),
  ('SUN', 'Sunday');

INSERT INTO
  LIV.frequencies (frequencyId, frequencyName)
VALUES
  ('D', 'Daily'),
  ('W', 'Weekly'),
  ('N', 'None');