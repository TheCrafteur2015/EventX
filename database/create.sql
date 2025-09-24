CREATE DATABASE eventx;

CREATE TABLE role(
   id_role INT,
   role_name VARCHAR(256) NOT NULL,
   created_at DATETIME NOT NULL,
   updated_at DATETIME NOT NULL,
   PRIMARY KEY(id_role),
   UNIQUE(role_name)
);

CREATE TABLE Users(
   id_user INT,
   mail VARCHAR(256) NOT NULL,
   password VARCHAR(256) NOT NULL,
   is_active BOOLEAN,
   created_at DATETIME NOT NULL,
   updated_at DATETIME NOT NULL,
   id_role INT NOT NULL,
   PRIMARY KEY(id_user),
   UNIQUE(mail),
   UNIQUE(password),
   FOREIGN KEY(id_role) REFERENCES role(id_role)
);

CREATE TABLE profile(
   id_profile INT,
   firstname VARCHAR(256) NOT NULL,
   lastname VARCHAR(256) NOT NULL,
   picture VARCHAR(512),
   profile_description TEXT,
   created_at DATETIME NOT NULL,
   updated_at DATETIME NOT NULL,
   id_user INT NOT NULL,
   PRIMARY KEY(id_profile),
   UNIQUE(id_user),
   FOREIGN KEY(id_user) REFERENCES Users(id_user)
);

CREATE TABLE event(
   id_event INT,
   event_name VARCHAR(256) NOT NULL,
   event_description TEXT,
   start_date DATETIME NOT NULL,
   end_date DATETIME NOT NULL,
   location VARCHAR(512) NOT NULL,
   created_at DATETIME NOT NULL,
   updated_at DATETIME NOT NULL,
   id_user INT NOT NULL,
   PRIMARY KEY(id_event),
   FOREIGN KEY(id_user) REFERENCES Users(id_user)
);

CREATE TABLE wishlist(
   id_user INT,
   id_event INT,
   PRIMARY KEY(id_user, id_event),
   FOREIGN KEY(id_user) REFERENCES Users(id_user),
   FOREIGN KEY(id_event) REFERENCES event(id_event)
);

CREATE TABLE registration(
   id_user INT,
   id_event INT,
   PRIMARY KEY(id_user, id_event),
   FOREIGN KEY(id_user) REFERENCES Users(id_user),
   FOREIGN KEY(id_event) REFERENCES event(id_event)
);