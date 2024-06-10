drop schema if exists LIV;

create schema LIV;

create table
  LIV.users (
    userId int auto_increment primary key,
    userName varchar(255) not null,
    email varchar(255) not null,
    password varchar(255) not null,
    createDate timestamp default CURRENT_TIMESTAMP null,
    photo text null
  );

create table
  LIV.habits (
    habitId int auto_increment primary key,
    userId int not null,
    habitName varchar(255) not null,
    color varchar(255) not null,
    icon varchar(255) null,
    frequencyId varchar(5) null,
    habitGoal int null,
    habitGoalUnit varchar(255) null,
    enabled tinyint (1) default 1 null,
    createDate datetime default CURRENT_TIMESTAMP null
  );

create table
  LIV.habitsWeekDays (
    habitId int null,
    weekdayId varchar(5) null,
    constraint habitsWeekDays_pk unique (weekdayId, habitId)
  );

create table
  LIV.habitRecords (
    habitId int not null,
    userId int not null,
    recordDate date not null,
    record int not null,
    primary key (habitId, userId, recordDate)
  );

create table
  LIV.frequencies (
    frequencyId varchar(5) not null primary key,
    frequencyName varchar(100) not null
  );

INSERT INTO
  LIV.frequencies (frequencyId, frequencyName)
VALUES
  ('D', 'Diario'),
  ('W', 'Semanal');

create table
  LIV.daysOfMonth (dayNumber int not null primary key);

INSERT INTO
  LIV.daysOfMonth (dayNumber)
VALUES
  (1),
  (2),
  (3),
  (4),
  (5),
  (6),
  (7),
  (8),
  (9),
  (10),
  (11),
  (12),
  (13),
  (14),
  (15),
  (16),
  (17),
  (18),
  (19),
  (20),
  (21),
  (22),
  (23),
  (24),
  (25),
  (26),
  (27),
  (28),
  (29),
  (30),
  (31);

create table
  LIV.defaultColors (color varchar(20) null);

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

create table
  LIV.weekDays (
    weekdayId int not null primary key,
    weekdayAlias varchar(5) null,
    weekdayName varchar(20) null
  );

INSERT INTO
  LIV.weekDays (weekdayId, weekdayAlias, weekdayName)
VALUES
  (0, 'L', 'Lunes'),
  (1, 'M', 'Martes'),
  (2, 'X', 'Miercoles'),
  (3, 'J', 'Jueves'),
  (4, 'V', 'Viernes'),
  (5, 'S', 'Sabado'),
  (6, 'D', 'Domingo');