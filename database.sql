CREATE DATABASE formule1 ;

USE formule1 ;

CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    news_content TEXT NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT, 
    username VARCHAR(255) NOT NULL, 
    password_hash VARCHAR(255) NOT NULL 
);

CREATE TABLE drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT, 
    first_name VARCHAR(50) NOT NULL,          
    last_name VARCHAR(50) NOT NULL,           
    nationality VARCHAR(50) NOT NULL,         
    date_of_birth DATE,                   
    driver_number INT UNIQUE,                 
    team_name VARCHAR(100),                   
    championships_won INT DEFAULT 0,          
    career_points DECIMAL(10, 2) DEFAULT 0.00,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE            
);

ALTER TABLE drivers
ADD COLUMN image VARCHAR(255); 

ALTER TABLE drivers
ADD COLUMN flag_url VARCHAR(255) NULL;

ALTER TABLE drivers
ADD COLUMN driver_color VARCHAR(50) NULL;

CREATE TABLE teams (
    team_id INT PRIMARY KEY AUTO_INCREMENT,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    team_color VARCHAR(7) NOT NULL,
    full_team_name VARCHAR(255),
    base_location VARCHAR(100),
    team_principal VARCHAR(100),
    technical_director VARCHAR(100),
    championships_won INT DEFAULT 0,
    first_entry_year INT,
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    current_engine_supplier VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Red Bull Racing', '#0600EF', 'Oracle Red Bull Racing', 'Milton Keynes, UK', 'Christian Horner', 'Pierre Waché', 7, 2005, 'https://www.redbullracing.com/', 'url_naar_redbull_logo.png', 'Honda RBPT', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Mercedes', '#00D2BE', 'Mercedes-AMG Petronas Formula One Team', 'Brackley, UK', 'Toto Wolff', 'James Allison', 8, 2010, 'https://www.mercedesamgf1.com/', 'url_naar_mercedes_logo.png', 'Mercedes', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Ferrari', '#DC0000', 'Scuderia Ferrari', 'Maranello, Italy', 'Frédéric Vasseur', 'Enrico Cardile', 16, 1950, 'https://www.ferrari.com/en-EN/formula1', 'url_naar_ferrari_logo.png', 'Ferrari', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('McLaren', '#FF8700', 'McLaren Formula 1 Team', 'Woking, UK', 'Andrea Stella', 'Peter Prodromou', 8, 1966, 'https://www.mclaren.com/racing/formula-1/', 'url_naar_mclaren_logo.png', 'Mercedes', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Aston Martin', '#006F62', 'Aston Martin Aramco Formula One Team', 'Silverstone, UK', 'Mike Krack', 'Dan Fallows', 0, 2021, 'https://www.astonmartinf1.com/', 'url_naar_aston_martin_logo.png', 'Mercedes', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Alpine', '#0090FF', 'BWT Alpine F1 Team', 'Enstone, UK', 'Bruno Famin', 'Matt Harman', 0, 2021, 'https://www.alpinecars.com/en/f1/', 'url_naar_alpine_logo.png', 'Renault', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Williams', '#64C4FF', 'Williams Racing', 'Grove, UK', 'James Vowles', 'Pat Fry', 9, 1977, 'https://www.williamsf1.com/', 'url_naar_williams_logo.png', 'Mercedes', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Haas', '#B6BABC', 'MoneyGram Haas F1 Team', 'Kannapolis, USA', 'Ayao Komatsu', 'Simone Resta', 0, 2016, 'https://www.haasf1team.com/', 'url_naar_haas_logo.png', 'Ferrari', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('RB', '#6692FF', 'Visa Cash App RB Formula 1 Team', 'Faenza, Italy', 'Laurent Mekies', 'Jody Egginton', 0, 2006, 'https://www.visacashapprb.com/', 'url_naar_rb_logo.png', 'Honda RBPT', TRUE);

INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Sauber', '#52E252', 'Stake F1 Team Kick Sauber', 'Hinwil, Switzerland', 'Alessandro Alunni Bravi', 'James Key', 0, 1993, 'https://www.sauber-group.com/motorsport/formula-1/', 'url_naar_sauber_logo.png', 'Ferrari', TRUE);

ALTER TABLE drivers
ADD COLUMN team_id INT,
ADD CONSTRAINT fk_team
FOREIGN KEY (team_id) REFERENCES teams(team_id);

ALTER TABLE drivers
ADD COLUMN place_of_birth VARCHAR(255);

ALTER TABLE drivers
ADD COLUMN description VARCHAR(255);


INSERT INTO drivers (first_name, last_name, nationality, date_of_birth, driver_number, team_name, championships_won, career_points, image, is_active) VALUES
('Max', 'Verstappen', 'Dutch', '1997-09-30', 1, 'Red Bull Racing', 3, 2850.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Max+Verstappen', TRUE),
('Lewis', 'Hamilton', 'British', '1985-01-07', 44, 'Ferrari', 7, 4850.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lewis+Hamilton', TRUE), 
('Charles', 'Leclerc', 'Monegasque', '1997-10-16', 16, 'Ferrari', 0, 1400.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Charles+Leclerc', TRUE),
('Lando', 'Norris', 'British', '1999-11-13', 4, 'McLaren', 0, 950.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lando+Norris', TRUE),
('Fernando', 'Alonso', 'Spanish', '1981-07-29', 14, 'Aston Martin', 2, 2350.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Fernando+Alonso', TRUE),
('George', 'Russell', 'British', '1998-02-15', 63, 'Mercedes', 0, 650.00, 'https://placehold.co/150x150/000000/FFFFFF?text=George+Russell', TRUE),
('Yuki', 'Tsunoda', 'Japanese', '2000-05-11', 22, 'Red Bull Racing', 0, 200.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Yuki+Tsunoda', TRUE),
('Carlos', 'Sainz', 'Spanish', '1994-09-01', 55, 'Williams', 0, 1100.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Carlos+Sainz', TRUE), 
('Oscar', 'Piastri', 'Australian', '2001-04-06', 81, 'McLaren', 0, 300.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Oscar+Piastri', TRUE),
('Nico', 'Hulkenberg', 'German', '1987-08-19', 27, 'Sauber', 0, 580.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Nico+Hulkenberg', TRUE), 
('Lance', 'Stroll', 'Canadian', '1998-10-29', 18, 'Aston Martin', 0, 320.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lance+Stroll', TRUE),
('Alexander', 'Albon', 'Thai', '1996-03-23', 23, 'Williams', 0, 220.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Alexander+Albon', TRUE),
('Esteban', 'Ocon', 'French', '1996-09-17', 31, 'Haas', 0, 420.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Esteban+Ocon', TRUE), 
('Pierre', 'Gasly', 'French', '1996-02-07', 10, 'Alpine', 0, 480.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Pierre+Gasly', TRUE),
('Liam', 'Lawson', 'New Zealander', '2002-02-11', 30, 'RB', 0, 10.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Liam+Lawson', TRUE),
('Franco', 'Colapinto', 'Argentijn', '2002-02-11', 43, 'Alpine', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Liam+Lawson', TRUE); 

INSERT INTO drivers (first_name, last_name, nationality, date_of_birth, driver_number, team_name, championships_won, career_points, image, is_active) VALUES
('Andrea Kimi', 'Antonelli', 'Italian', '2006-08-25', 12, 'Mercedes', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Kimi+Antonelli', TRUE),
('Oliver', 'Bearman', 'British', '2005-05-08', 87, 'Haas', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Oliver+Bearman', TRUE),
('Jack', 'Doohan', 'Australian', '2003-01-20', 7, 'Alpine', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Jack+Doohan', TRUE),
('Gabriel', 'Bortoleto', 'Brazilian', '2004-10-14', 5, 'Sauber', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Gabriel+Bortoleto', TRUE),
('Isack', 'Hadjar', 'French', '2004-09-28', 6, 'RB', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Isack+Hadjar', TRUE);

CREATE TABLE circuits (
    circuit_key VARCHAR(50) PRIMARY KEY,       
    title VARCHAR(100) NOT NULL,               
    grandprix VARCHAR(100) NOT NULL,           
    location VARCHAR(100) NOT NULL,            
    map_url VARCHAR(255),                      
    first_gp_year INT,                         
    lap_count INT,                             
    circuit_length_km DECIMAL(10, 3),          
    race_distance_km DECIMAL(10, 3),           
    lap_record VARCHAR(100),                   
    description TEXT,                          
    highlights TEXT                            
);

INSERT INTO circuits (
    circuit_key, title, grandprix, location, map_url, first_gp_year,
    lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights
) VALUES
('australia', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Australia_Circuit.png', 1996, 58, 5.278, 306.124, '1:19.813 (Charles Leclerc, 2024)', 'Een semi-stratencircuit rondom het Albert Park Lake, bekend om zijn snelle, vloeiende lay-out en uitdagende bochten. Het is vaak de seizoensopener.', 'Semi-stratencircuit, Snelle en vloeiende lay-out, Vaak seizoensopener.'),
('china', 'Shanghai International Circuit', 'Chinese Grand Prix', 'Shanghai, China', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/China_Circuit.png', 2004, 56, 5.451, 305.066, '1:32.238 (Michael Schumacher, 2004)', 'Ontworpen met de vorm van het Chinese karakter "Shang" (boven/omhoog), staat bekend om zijn unieke bochtencombinaties en lange rechte stukken.', 'Unieke "Shang" vorm, Lange rechte stukken, Uitdagende bochtencombinaties.'),
('japan', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Japan_Circuit.png', 1987, 53, 5.807, 307.471, '1:30.983 (Lewis Hamilton, 2019)', 'Een favoriet onder coureurs vanwege zijn unieke "achtbaan"-lay-out met een crossover. Bekend om zijn snelle, technische secties en de iconische 130R bocht.', 'Unieke crossover lay-out, Technische en snelle bochten, Iconische 130R.'),
('bahrain', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrein', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Bahrain_Circuit.png', 2004, 57, 5.412, 308.238, '1:31.447 (Pedro de la Rosa, 2005)', 'Een modern circuit in de woestijn, vaak de openingsrace. Bekend om zijn races onder kunstlicht en de uitdaging van zand op de baan.', 'Nachtrace onder kunstlicht, Woestijnomgeving, Zand op de baan.'),
('saudi_arabia', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saoedi-Arabië', 'https://www.formula1.com/content/dam/fom-website/2021/Saudi%20Arabia/jeddah-circuit-map.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit op de kalender, met veel blinde bochten en hoge snelheden langs de kustlijn.', 'Snelste stratencircuit, Veel blinde bochten, Hoge snelheden.'),
('miami', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami Gardens, VS', 'https://www.formula1.com/content/dam/fom-website/2022/Miami/Miami_Circuit.png', 2022, 57, 5.412, 308.370, '1:29.708 (Max Verstappen, 2023)', 'Een speciaal gebouwd circuit rondom het Hard Rock Stadium, met een mix van snelle secties en krappe bochten, en een iconische "nep-haven".', 'Speciaal gebouwd circuit, Mix van snelle en krappe bochten, "Nep-haven" feature.'),
('emilia_romagna', 'Autodromo Internazionale Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Imola_Circuit.png', 1980, 63, 4.909, 309.267, '1:15.484 (Lewis Hamilton, 2020)', 'Een historisch circuit met een ouderwetse lay-out, smal en uitdagend, bekend om zijn snelle chicanes en de Tamburello-bocht.', 'Historisch circuit, Smal en uitdagend, Snelle chicanes.'),
('monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Monaco_Circuit.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest glamoureuze en veeleisende stratencircuit, waar precisie en moed essentieel zijn. Overtaken is hier extreem moeilijk.', 'Glamoureus stratencircuit, Zeer veeleisend, Overtaken is moeilijk.'),
('spain', 'Circuit de Barcelona-Catalunya', 'Spanish Grand Prix', 'Barcelona, Spanje', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Spain_Circuit.png', 1991, 66, 4.675, 308.424, '1:18.149 (Max Verstappen, 2021)', 'Dit circuit is een vaste waarde op de kalender en staat bekend om zijn uitgebreide testmogelijkheden in het voorseizoen. Het biedt een mix van snelle en langzame bochten, wat het een goede indicator maakt voor de algemene prestaties van een auto. De lange laatste bocht is cruciaal voor de banden.', 'Vaak gebruikt voor wintertests, Mix van snelle en langzame secties, De laatste sector is cruciaal voor bandenmanagement.'),
('canada', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Canada_Circuit.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Gelegen op een eiland in Montreal, bekend om zijn lange rechte stukken, krappe chicanes en de beruchte "Wall of Champions".', 'Gelegen op een eiland, Lange rechte stukken, "Wall of Champions".'),
('austria', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Oostenrijk', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Austria_Circuit.png', 1970, 71, 4.318, 306.452, '1:05.619 (Carlos Sainz, 2020)', 'Een kort, snel en heuvelachtig circuit met veel hoogteverschillen, wat zorgt voor spectaculaire inhaalacties.', 'Kort en snel circuit, Veel hoogteverschillen, Spectaculaire inhaalacties.'),
('great_britain', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Groot-Brittannië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Great_Britain_Circuit.png', 1950, 52, 5.891, 306.198, '1:27.097 (Max Verstappen, 2020)', 'De thuisbasis van de Britse motorsport, bekend om zijn snelle, vloeiende secties zoals Maggotts, Becketts en Chapel.', 'Historisch circuit, Snelle en vloeiende secties, Iconische bochtencombinaties.'),
('belgium', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, België', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Belgium_Circuit.png', 1950, 44, 7.004, 308.052, '1:46.286 (Valtteri Bottas, 2018)', 'Een van de meest legendarische en langste circuits, bekend om zijn snelle, vloeiende lay-out, hoogteverschillen en de iconische Eau Rouge-Raidillon combinatie.', 'Langste circuit op de kalender, Veel hoogteverschillen, Iconische Eau Rouge.'),
('hungary', 'Hungaroring', 'Hungarian Grand Prix', 'Mogyoród, Hongarije', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Hungary_Circuit.png', 1986, 70, 4.381, 306.670, '1:16.627 (Lewis Hamilton, 2020)', 'Een bochtig circuit dat vaak wordt vergeleken met een kartbaan, waar inhalen lastig is en de nadruk ligt op aerodynamische grip.', 'Bochtig en technisch, Moeilijk om in te halen, Hoge aerodynamische grip vereist.'),
('netherlands', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Nederland', 'https://www.formula1.com/content/dam/fom-website/2020/Netherlands/Zandvoort_Circuit.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een klassiek circuit met duinen en banking bochten, bekend om zijn uitdagende lay-out en de enthousiaste Nederlandse fans.', 'Circuit in de duinen, Banking bochten, Enthousiaste fans.'),
('italy', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Italy_Circuit.png', 1950, 53, 5.793, 306.720, '1:21.046 (Rubens Barrichello, 2004)', 'De "Temple of Speed", bekend om zijn lange rechte stukken en snelle chicanes, waar topsnelheid cruciaal is.', 'De "Temple of Speed", Lange rechte stukken, Snelle chicanes.'),
('azerbaijan', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbeidzjan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Azerbaijan_Circuit.png', 2016, 51, 6.003, 306.049, '1:43.009 (Charles Leclerc, 2019)', 'Een stratencircuit met een mix van extreem lange rechte stukken en een zeer smal, technisch gedeelte rondom het kasteel.', 'Stratencircuit, Extreem lange rechte stukken, Smal kasteelgedeelte.'),
('singapore', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Singapore_Circuit.png', 2008, 62, 5.063, 313.870, '1:35.867 (Lewis Hamilton, 2018)', 'Het eerste nachtrace-circuit in de F1-geschiedenis, bekend om zijn hoge luchtvochtigheid, hobbelige oppervlak en fysiek veeleisende lay-out.', 'Eerste nachtrace, Hoge luchtvochtigheid, Fysiek veeleisend.'),
('usa', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, VS', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/USA_Circuit.png', 2012, 56, 5.513, 308.405, '1:36.169 (Charles Leclerc, 2019)', 'Een modern circuit met een unieke mix van secties geïnspireerd op andere beroemde circuits, inclusief een steile klim naar bocht 1.', 'Moderne lay-out, Geïnspireerd op andere circuits, Steile klim naar bocht 1.'),
('mexico', 'Autódromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico-Stad, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Mexico_Circuit.png', 1963, 71, 4.304, 305.354, '1:17.774 (Valtteri Bottas, 2021)', 'Gelegen op grote hoogte, wat de motoren en aerodynamica uitdaagt. Bekend om de sfeervolle Foro Sol-stadionsectie.', 'Hoge hoogte, Uitdaging voor motoren/aerodynamica, Foro Sol stadion.'),
('brazil', 'Autódromo José Carlos Pace', 'São Paulo Grand Prix', 'São Paulo, Brazilië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Brazil_Circuit.png', 1973, 71, 4.309, 305.909, '1:10.540 (Valtteri Bottas, 2018)', 'Een historisch circuit met een korte, intense lay-out en veel hoogteverschillen, vaak het toneel van dramatische races.', 'Historisch circuit, Korte en intense lay-out, Dramatische races.'),
('las_vegas', 'Las Vegas Strip Circuit', 'Las Vegas Grand Prix', 'Paradise, VS', 'https://www.formula1.com/content/dam/fom-website/2023/Las%20Vegas/Las_Vegas_Circuit.png', 2023, 50, 6.201, 309.958, '1:34.876 (Lando Norris, 2024)', 'Een nieuw stratencircuit dat over de beroemde Las Vegas Strip loopt, bekend om zijn hoge snelheden en de spectaculaire nachtelijke setting.', 'Nieuw stratencircuit, Over de Las Vegas Strip, Spectaculaire nachtrace.'),
('qatar', 'Lusail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2023/Qatar/Qatar_Circuit.png', 2021, 57, 5.419, 308.611, '1:22.384 (Lando Norris, 2024)', 'Een modern circuit in de woestijn, bekend om zijn snelle, vloeiende lay-out en de race onder kunstlicht.', 'Modern woestijncircuit, Snelle en vloeiende lay-out, Nachtrace.'),
('abu_dhabi', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, VAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Abu_Dhabi_Circuit.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met indrukwekkende faciliteiten, bekend om zijn jachthaven en de race die begint bij zonsondergang en eindigt in het donker.', 'Moderne faciliteiten, Jachthaven setting, Zonsondergang naar nachtrace.');

ALTER TABLE circuits ADD COLUMN calendar_order INT UNIQUE;

UPDATE circuits SET calendar_order = 1 WHERE circuit_key = 'australia';
UPDATE circuits SET calendar_order = 2 WHERE circuit_key = 'china';
UPDATE circuits SET calendar_order = 3 WHERE circuit_key = 'japan';
UPDATE circuits SET calendar_order = 4 WHERE circuit_key = 'bahrain';
UPDATE circuits SET calendar_order = 5 WHERE circuit_key = 'saudi_arabia';
UPDATE circuits SET calendar_order = 6 WHERE circuit_key = 'miami';
UPDATE circuits SET calendar_order = 7 WHERE circuit_key = 'emilia_romagna';
UPDATE circuits SET calendar_order = 8 WHERE circuit_key = 'monaco';
UPDATE circuits SET calendar_order = 9 WHERE circuit_key = 'spain';
UPDATE circuits SET calendar_order = 10 WHERE circuit_key = 'canada';
UPDATE circuits SET calendar_order = 11 WHERE circuit_key = 'austria';
UPDATE circuits SET calendar_order = 12 WHERE circuit_key = 'great_britain';
UPDATE circuits SET calendar_order = 13 WHERE circuit_key = 'belgium';
UPDATE circuits SET calendar_order = 14 WHERE circuit_key = 'hungary';
UPDATE circuits SET calendar_order = 15 WHERE circuit_key = 'netherlands';
UPDATE circuits SET calendar_order = 16 WHERE circuit_key = 'italy';
UPDATE circuits SET calendar_order = 17 WHERE circuit_key = 'azerbaijan';
UPDATE circuits SET calendar_order = 18 WHERE circuit_key = 'singapore';
UPDATE circuits SET calendar_order = 19 WHERE circuit_key = 'usa';
UPDATE circuits SET calendar_order = 20 WHERE circuit_key = 'mexico';
UPDATE circuits SET calendar_order = 21 WHERE circuit_key = 'brazil';
UPDATE circuits SET calendar_order = 22 WHERE circuit_key = 'las_vegas';
UPDATE circuits SET calendar_order = 23 WHERE circuit_key = 'qatar';
UPDATE circuits SET calendar_order = 24 WHERE circuit_key = 'abu_dhabi';

CREATE TABLE race_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_key VARCHAR(50) NOT NULL,
    driver_id INT NOT NULL,
    race_year INT NOT NULL,
    race_type ENUM('Race', 'Sprint') NOT NULL,
    position INT NOT NULL,       
    points DECIMAL(5,2) NOT NULL DEFAULT 0.00, 
    laps_completed INT NOT NULL,
    finish_status VARCHAR(50),
    fastest_lap_time VARCHAR(20),     
    time_offset VARCHAR(50),
    pole_position BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (circuit_key) REFERENCES circuits(circuit_key),
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id),
    UNIQUE (circuit_key, race_year, race_type, driver_id) 
);

ALTER TABLE circuits
ADD COLUMN race_year INT;

CREATE TABLE points_system (
    position INT PRIMARY KEY,
    points DECIMAL(5,2) NOT NULL
);

INSERT INTO points_system (position, points) VALUES
(1, 25.00),
(2, 18.00),
(3, 15.00),
(4, 12.00),
(5, 10.00),
(6, 8.00),
(7, 6.00),
(8, 4.00),
(9, 2.00),
(10, 1.00);

INSERT INTO points_system (position, points) VALUES
(11, 0.00),
(12, 0.00),
(13, 0.00),
(14, 0.00),
(15, 0.00),
(16, 0.00),
(17, 0.00),
(18, 0.00),
(19, 0.00),
(20, 0.00);

SELECT
    d.driver_id, d.first_name, d.last_name, t.team_name
FROM
    drivers d
JOIN
    teams t ON d.team_name = t.team_name; 

CREATE TABLE sprint_points_system (
    position INT PRIMARY KEY NOT NULL,
    points DECIMAL(5,2) NOT NULL
);

INSERT INTO sprint_points_system (position, points) VALUES
(1, 8.00),
(2, 7.00),
(3, 6.00),
(4, 5.00),
(5, 4.00),
(6, 3.00),
(7, 2.00),
(8, 1.00),
(9, 0.00),
(10, 0.00),
(11, 0.00),
(12, 0.00),
(13, 0.00),
(14, 0.00),
(15, 0.00),
(16, 0.00),
(17, 0.00),
(18, 0.00),
(19, 0.00),
(20, 0.00);

CREATE TABLE race_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_key VARCHAR(50) NOT NULL,
    driver_id INT NOT NULL,
    race_year INT NOT NULL,
    race_type ENUM('Race', 'Sprint') NOT NULL,
    position INT NOT NULL,
    points DECIMAL(5,2),
    laps_completed INT,
    finish_status VARCHAR(50),
    fastest_lap_time VARCHAR(20),
    time_offset VARCHAR(20),
    pole_position BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY UQ_race_position (circuit_key, race_year, race_type, position),
    FOREIGN KEY (circuit_key) REFERENCES circuits(circuit_key),
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id)
);

ALTER TABLE circuits
ADD COLUMN race_datetime DATETIME; 

UPDATE circuits SET race_datetime = '2025-03-16 06:00:00' WHERE circuit_key = 'australia';    
UPDATE circuits SET race_datetime = '2025-03-23 07:00:00' WHERE circuit_key = 'china';         
UPDATE circuits SET race_datetime = '2025-04-06 06:00:00' WHERE circuit_key = 'japan';         
UPDATE circuits SET race_datetime = '2025-04-13 14:00:00' WHERE circuit_key = 'bahrain';       
UPDATE circuits SET race_datetime = '2025-04-20 17:00:00' WHERE circuit_key = 'saudi_arabia'; 
UPDATE circuits SET race_datetime = '2025-05-04 19:30:00' WHERE circuit_key = 'miami';         
UPDATE circuits SET race_datetime = '2025-05-18 13:00:00' WHERE circuit_key = 'emilia_romagna';
UPDATE circuits SET race_datetime = '2025-05-25 13:00:00' WHERE circuit_key = 'monaco';        
UPDATE circuits SET race_datetime = '2025-06-01 13:00:00' WHERE circuit_key = 'spain';         
UPDATE circuits SET race_datetime = '2025-06-15 18:00:00' WHERE circuit_key = 'canada';       
UPDATE circuits SET race_datetime = '2025-06-29 13:00:00' WHERE circuit_key = 'austria';       
UPDATE circuits SET race_datetime = '2025-07-06 14:00:00' WHERE circuit_key = 'great_britain'; 
UPDATE circuits SET race_datetime = '2025-07-27 13:00:00' WHERE circuit_key = 'belgium';       
UPDATE circuits SET race_datetime = '2025-08-03 13:00:00' WHERE circuit_key = 'hungary';      
UPDATE circuits SET race_datetime = '2025-08-31 13:00:00' WHERE circuit_key = 'netherlands';  
UPDATE circuits SET race_datetime = '2025-09-07 13:00:00' WHERE circuit_key = 'italy';        
UPDATE circuits SET race_datetime = '2025-09-21 11:00:00' WHERE circuit_key = 'azerbaijan';    
UPDATE circuits SET race_datetime = '2025-10-05 12:00:00' WHERE circuit_key = 'singapore';     
UPDATE circuits SET race_datetime = '2025-10-19 19:00:00' WHERE circuit_key = 'usa';          
UPDATE circuits SET race_datetime = '2025-10-26 19:00:00' WHERE circuit_key = 'mexico';      
UPDATE circuits SET race_datetime = '2025-11-09 16:00:00' WHERE circuit_key = 'brazil';      
UPDATE circuits SET race_datetime = '2025-11-22 06:00:00' WHERE circuit_key = 'las_vegas';
UPDATE circuits SET race_datetime = '2025-11-30 14:00:00' WHERE circuit_key = 'qatar';        
UPDATE circuits SET race_datetime = '2025-12-07 13:00:00' WHERE circuit_key = 'abu_dhabi';     

ALTER TABLE news
ADD COLUMN date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE news
ADD COLUMN source VARCHAR(255) NULL;

ALTER TABLE news
ADD COLUMN keywords VARCHAR(255) NULL;

ALTER TABLE news
CHANGE COLUMN id news_id INT AUTO_INCREMENT;

ALTER TABLE news
MODIFY COLUMN image_url TEXT;

CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'binck@guus2025';
GRANT ALL PRIVILEGES ON formule1.* TO 'webuser'@'localhost';
FLUSH PRIVILEGES;

-- Voeg de 2024 kalenderdata toe aan de 'circuits' tabel-- nog doen andere databases!--
ALTER TABLE circuits
DROP INDEX calendar_order;

INSERT INTO circuits (circuit_key, title, grandprix, location, map_url, first_gp_year, lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights, calendar_order, race_year, race_datetime) VALUES
('bahrain_international_circuit', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Bahrain_Circuit.png.transform/content-image-full-width/image.png', 2004, 57, 5.412, 308.238, '1:31.447 (Pedro de la Rosa, 2005)', 'Een modern circuit met een unieke mix van snelle rechte stukken en uitdagende bochten.', 'De eerste nachtrace van het seizoen, bekend om zijn spectaculaire start en strategieën.', 1, 2024, '2024-03-02 16:00:00'),
('jeddah_corniche_circuit', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saudi Arabia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Saudi_Arabia_Circuit.png.transform/content-image-full-width/image.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit ter wereld, gelegen aan de Rode Zee.', 'Snelle, vloeiende bochten en nauwe passages, wat zorgt voor een hoge snelheid en weinig marge voor fouten.', 2, 2024, '2024-03-09 18:00:00'),
('albert_park_circuit', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Australia_Circuit.png.transform/content-image-full-width/image.png', 1996, 58, 5.278, 306.124, '1:20.260 (Sergio Pérez, 2023)', 'Een semi-stratencircuit rondom een meer in een stadspark.', 'Een race die vaak onvoorspelbaar is vanwege zijn uitdagende lay-out en de mogelijkheid van safety cars.', 3, 2024, '2024-03-24 05:00:00'),
('suzuka_international_racing_course', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Japan_Circuit.png.transform/content-image-full-width/image.png', 1987, 53, 5.807, 307.771, '1:30.983 (Lewis Hamilton, 2019)', 'Een klassiek en technisch circuit, beroemd om de achtvormige lay-out.', 'De iconische S-bochten en de 130R bocht zijn een ware test voor de coureurs.', 4, 2024, '2024-04-07 06:00:00'),
('shanghai_international_circuit', 'Shanghai International Circuit', 'Chinese Grand Prix', 'Shanghai, China', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/China_Circuit.png.transform/content-image-full-width/image.png', 2004, 56, 5.451, 305.066, '1:32.238 (Michael Schumacher, 2004)', 'Een modern circuit met een unieke architectuur en lange rechte stukken.', 'Het is de terugkeer van de Chinese GP na een lange afwezigheid.', 5, 2024, '2024-04-21 08:00:00'),
('miami_international_autodrome', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Miami_Circuit.png.transform/content-image-full-width/image.png', 2022, 57, 5.412, 308.326, '1:29.708 (Max Verstappen, 2023)', 'Een stratencircuit rondom het Hard Rock Stadium, met een unieke sfeer.', 'De mix van snelle en langzame secties maakt het een uitdagend circuit voor de coureurs.', 6, 2024, '2024-05-05 20:00:00'),
('autodromo_enzo_e_dino_ferrari', 'Autodromo Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Imola_Circuit.png.transform/content-image-full-width/image.png', 1980, 63, 4.909, 309.049, '1:15.484 (Lewis Hamilton, 2020)', 'Een klassiek Europees circuit met een rijke geschiedenis.', 'Een smal en technisch circuit met snelle chicanes en weinig inhaalmogelijkheden.', 7, 2024, '2024-05-19 15:00:00'),
('circuit_de_monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monte Carlo, Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Monaco_Circuit.png.transform/content-image-full-width/image.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest iconische stratencircuit, waar de coureur het verschil maakt.', 'Een race van uithoudingsvermogen en precisie, met de beroemde tunnel en de Casino Square.', 8, 2024, '2024-05-26 15:00:00'),
('circuit_gilles_villeneuve', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Canada_Circuit.png.transform/content-image-full-width/image.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Een semi-stratencircuit op het Île Notre-Dame in Montreal.', 'Beroemd om de "Wall of Champions" en de onvoorspelbare weersomstandigheden.', 9, 2024, '2024-06-09 20:00:00'),
('circuit_de_catalunya', 'Circuit de Catalunya', 'Spanish Grand Prix', 'Barcelona, Spain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Spain_Circuit.png.transform/content-image-full-width/image.png', 1991, 66, 4.657, 307.230, '1:16.330 (Max Verstappen, 2023)', 'Een testcircuit dat bekend staat om zijn snelle chicanes en lange bochten.', 'Dit circuit wordt vaak gebruikt voor wintertests, dus de teams kennen het goed.', 10, 2024, '2024-06-23 15:00:00'),
('red_bull_ring', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Austria', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Austria_Circuit.png.transform/content-image-full-width/image.png', 1970, 71, 4.318, 306.452, '1:05.619 (Carlos Sainz, 2020)', 'Een pittoresk circuit in de heuvels van Oostenrijk.', 'Kort en snel met veel hoogteverschillen, wat leidt tot spannende gevechten.', 11, 2024, '2024-06-30 15:00:00'),
('silverstone_circuit', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Great Britain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Great_Britain_Circuit.png.transform/content-image-full-width/image.png', 1950, 52, 5.891, 306.198, '1:27.097 (Max Verstappen, 2020)', 'De thuisbasis van de Britse motorsport en een van de snelste circuits op de kalender.', 'Een klassiek circuit met legendarische bochten zoals Copse, Maggotts en Becketts.', 12, 2024, '2024-07-07 16:00:00'),
('hungaroring', 'Hungaroring', 'Hungarian Grand Prix', 'Budapest, Hungary', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Hungary_Circuit.png.transform/content-image-full-width/image.png', 1986, 70, 4.381, 306.630, '1:16.627 (Lewis Hamilton, 2020)', 'Een krap en bochtig circuit waar inhalen erg moeilijk is.', 'Vaak vergeleken met Monaco, maar dan met meer ruimte en een hogere snelheid.', 13, 2024, '2024-07-21 15:00:00'),
('circuit_de_spa_francorchamps', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, Belgium', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Belgium_Circuit.png.transform/content-image-full-width/image.png', 1950, 44, 7.004, 308.052, '1:46.286 (Valtteri Bottas, 2018)', 'Een van de langste en meest geliefde circuits, met de beroemde Eau Rouge.', 'Bekend om zijn onvoorspelbare weer, dat vaak van het ene deel van het circuit verschilt van het andere.', 14, 2024, '2024-07-28 15:00:00'),
('circuit_zandvoort', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Netherlands', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Netherlands_Circuit.png.transform/content-image-full-width/image.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een compact en uitdagend duinencircuit met spectaculaire banking.', 'De sfeer is uniek met de oranje-gekleurde tribunes en het enthousiaste publiek.', 15, 2024, '2024-08-25 15:00:00'),
('autodromo_nazionale_monza', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Italy_Circuit.png.transform/content-image-full-width/image.png', 1950, 53, 5.793, 307.029, '1:21.046 (Rubens Barrichello, 2004)', 'De "Temple of Speed", een historisch circuit met lange rechte stukken.', 'Bekend om de hoge snelheden en de beroemde Tifosi, de gepassioneerde Ferrari-fans.', 16, 2024, '2024-09-01 15:00:00'),
('baku_city_circuit', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbaijan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Azerbaijan_Circuit.png.transform/content-image-full-width/image.png', 2016, 51, 6.003, 306.049, '1:43.009 (Charles Leclerc, 2019)', 'Een stratencircuit met een extreem lang recht stuk en een smalle, technische sectie.', 'Vaak vol met actie en safety cars vanwege de lastige bochten en hoge snelheden.', 17, 2024, '2024-09-15 13:00:00'),
('marina_bay_street_circuit', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Marina Bay, Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Singapore_Circuit.png.transform/content-image-full-width/image.png', 2008, 62, 5.063, 313.250, '1:41.905 (Kevin Magnussen, 2018)', 'De eerste nachtrace in de geschiedenis van de Formule 1.', 'De hoge temperaturen en luchtvochtigheid maken het een fysiek zware race voor de coureurs.', 18, 2024, '2024-09-22 14:00:00'),
('circuit_of_the_americas', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/USA_Circuit.png.transform/content-image-full-width/image.png', 2012, 56, 5.513, 308.405, '1:36.169 (Charles Leclerc, 2019)', 'Een modern circuit met veel hoogteverschillen en een indrukwekkende eerste bocht.', 'De combinatie van snelle, vloeiende secties en krappe haarspeldbochten zorgt voor spectaculaire races.', 19, 2024, '2024-10-20 21:00:00'),
('autodromo_hermanos_rodriguez', 'Autodromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico City, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Mexico_Circuit.png.transform/content-image-full-width/image.png', 1963, 71, 4.304, 305.354, '1:17.771 (Valtteri Bottas, 2021)', 'Een circuit op grote hoogte, wat de aerodynamica en motorkoeling beïnvloedt.', 'Bekend om het stadiongedeelte, waar duizenden fans de coureurs aanmoedigen.', 20, 2024, '2024-10-27 21:00:00'),
('interlagos', 'Interlagos', 'São Paulo Grand Prix', 'São Paulo, Brazil', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Brazil_Circuit.png.transform/content-image-full-width/image.png', 1973, 71, 4.309, 305.909, '1:10.540 (Lewis Hamilton, 2018)', 'Een historisch circuit met een spectaculair hoogteverschil en lange rechte stukken.', 'De ligging op een helling en de onvoorspelbare weersomstandigheden maken dit een klassieke race.', 21, 2024, '2024-11-03 17:00:00'),
('las_vegas_strip_circuit', 'Las Vegas Strip Circuit', 'Las Vegas Grand Prix', 'Las Vegas, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Las_Vegas_Circuit.png.transform/content-image-full-width/image.png', 2023, 50, 6.201, 310.000, '1:35.490 (Oscar Piastri, 2023)', 'Een nieuw stratencircuit over de beroemde Las Vegas Strip.', 'Een unieke nachtrace met hoge snelheden en een indrukwekkende achtergrond.', 22, 2024, '2024-11-23 07:00:00'),
('losail_international_circuit', 'Losail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Qatar_Circuit.png.transform/content-image-full-width/image.png', 2021, 57, 5.419, 308.680, '1:23.196 (Max Verstappen, 2023)', 'Een snel en vloeiend circuit met een lange rechte stuk.', 'De nachtrace en de zanderige omgeving zorgen voor een uitdagende race.', 23, 2024, '2024-12-01 17:00:00'),
('yas_marina_circuit', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, UAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Abu_Dhabi_Circuit.png.transform/content-image-full-width/image.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met spectaculaire architectuur, waaronder het Yas Hotel.', 'De traditionele seizoensafsluiter en de enige dag-tot-nachtrace op de kalender.', 24, 2024, '2024-12-08 14:00:00');

ALTER TABLE race_results DROP FOREIGN KEY race_results_ibfk_1;
ALTER TABLE circuits DROP PRIMARY KEY;
DELETE FROM circuits WHERE race_year IS NULL;
ALTER TABLE circuits ADD PRIMARY KEY (circuit_key, race_year);

INSERT IGNORE INTO circuits (circuit_key, title, grandprix, location, map_url, first_gp_year, lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights, calendar_order, race_year, race_datetime) VALUES
('bahrain_international_circuit', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Bahrain_Circuit.png.transform/content-image-full-width/image.png', 2004, 57, 5.412, 308.238, '1:33.729 (Guanyu Zhou, 2023)', 'Een modern circuit, bekend om zijn mix van snelle rechte stukken en uitdagende bochten.', 'De seizoensopener en een spectaculaire nachtrace.', 1, 2023, '2023-03-05 16:00:00'),
('jeddah_corniche_circuit', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saudi Arabia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Saudi_Arabia_Circuit.png.transform/content-image-full-width/image.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit, gelegen aan de Rode Zee.', 'Snelle, vloeiende bochten en nauwe passages, wat zorgt voor hoge snelheden en weinig foutmarge.', 2, 2023, '2023-03-19 18:00:00'),
('albert_park_circuit', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Australia_Circuit.png.transform/content-image-full-width/image.png', 1996, 58, 5.278, 306.124, '1:20.260 (Sergio Pérez, 2023)', 'Een semi-stratencircuit rondom een meer in een stadspark.', 'Een race die vaak onvoorspelbaar is vanwege safety cars en de uitdagende lay-out.', 3, 2023, '2023-04-02 07:00:00'),
('baku_city_circuit', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbaijan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Azerbaijan_Circuit.png.transform/content-image-full-width/image.png', 2016, 51, 6.003, 306.049, '1:43.009 (Charles Leclerc, 2019)', 'Een stratencircuit met een extreem lang recht stuk en een smalle, technische sectie.', 'Vaak vol met actie en safety cars vanwege de lastige bochten en hoge snelheden.', 4, 2023, '2023-04-30 13:00:00'),
('miami_international_autodrome', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Miami_Circuit.png.transform/content-image-full-width/image.png', 2022, 57, 5.412, 308.326, '1:29.708 (Max Verstappen, 2023)', 'Een stratencircuit rondom het Hard Rock Stadium, met een unieke sfeer.', 'De mix van snelle en langzame secties maakt het een uitdagend circuit voor de coureurs.', 5, 2023, '2023-05-07 21:30:00'),
('circuit_de_monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monte Carlo, Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Monaco_Circuit.png.transform/content-image-full-width/image.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest iconische stratencircuit, waar de coureur het verschil maakt.', 'Een race van uithoudingsvermogen en precisie, met de beroemde tunnel en de Casino Square.', 6, 2023, '2023-05-28 15:00:00'),
('circuit_de_catalunya', 'Circuit de Catalunya', 'Spanish Grand Prix', 'Barcelona, Spain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Spain_Circuit.png.transform/content-image-full-width/image.png', 1991, 66, 4.657, 307.230, '1:16.330 (Max Verstappen, 2023)', 'Een testcircuit dat bekend staat om zijn snelle chicanes en lange bochten.', 'Dit circuit wordt vaak gebruikt voor wintertests, dus de teams kennen het goed.', 7, 2023, '2023-06-04 15:00:00'),
('circuit_gilles_villeneuve', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Canada_Circuit.png.transform/content-image-full-width/image.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Een semi-stratencircuit op het Île Notre-Dame in Montreal.', 'Beroemd om de "Wall of Champions" en de onvoorspelbare weersomstandigheden.', 8, 2023, '2023-06-18 20:00:00'),
('red_bull_ring', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Austria', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Austria_Circuit.png.transform/content-image-full-width/image.png', 1970, 71, 4.318, 306.452, '1:05.619 (Carlos Sainz, 2020)', 'Een pittoresk circuit in de heuvels van Oostenrijk.', 'Kort en snel met veel hoogteverschillen, wat leidt tot spannende gevechten.', 9, 2023, '2023-07-02 15:00:00'),
('silverstone_circuit', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Great Britain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Great_Britain_Circuit.png.transform/content-image-full-width/image.png', 1950, 52, 5.891, 306.198, '1:30.275 (Max Verstappen, 2020)', 'De thuisbasis van de Britse motorsport en een van de snelste circuits op de kalender.', 'Een klassiek circuit met legendarische bochten zoals Copse, Maggotts en Becketts.', 10, 2023, '2023-07-09 16:00:00'),
('hungaroring', 'Hungaroring', 'Hungarian Grand Prix', 'Budapest, Hungary', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Hungary_Circuit.png.transform/content-image-full-width/image.png', 1986, 70, 4.381, 306.630, '1:20.573 (Oscar Piastri, 2023)', 'Een krap en bochtig circuit waar inhalen erg moeilijk is.', 'Vaak vergeleken met Monaco, maar dan met meer ruimte en een hogere snelheid.', 11, 2023, '2023-07-23 15:00:00'),
('circuit_de_spa_francorchamps', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, Belgium', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Belgium_Circuit.png.transform/content-image-full-width/image.png', 1950, 44, 7.004, 308.052, '1:47.305 (Lewis Hamilton, 2017)', 'Een van de langste en meest geliefde circuits, met de beroemde Eau Rouge.', 'Bekend om zijn onvoorspelbare weer, dat vaak van het ene deel van het circuit verschilt van het andere.', 12, 2023, '2023-07-30 15:00:00'),
('circuit_zandvoort', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Netherlands', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Netherlands_Circuit.png.transform/content-image-full-width/image.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een compact en uitdagend duinencircuit met spectaculaire banking.', 'De sfeer is uniek met de oranje-gekleurde tribunes en het enthousiaste publiek.', 13, 2023, '2023-08-27 15:00:00'),
('autodromo_nazionale_monza', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Italy_Circuit.png.transform/content-image-full-width/image.png', 1950, 53, 5.793, 307.029, '1:25.590 (Oscar Piastri, 2023)', 'De "Temple of Speed", een historisch circuit met lange rechte stukken.', 'Bekend om de hoge snelheden en de beroemde Tifosi, de gepassioneerde Ferrari-fans.', 14, 2023, '2023-09-03 15:00:00'),
('marina_bay_street_circuit', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Marina Bay, Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Singapore_Circuit.png.transform/content-image-full-width/image.png', 2008, 62, 4.940, 306.940, '1:35.490 (Lewis Hamilton, 2023)', 'De eerste nachtrace in de geschiedenis van de Formule 1.', 'De hoge temperaturen en luchtvochtigheid maken het een fysiek zware race voor de coureurs.', 15, 2023, '2023-09-17 14:00:00'),
('suzuka_international_racing_course', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Japan_Circuit.png.transform/content-image-full-width/image.png', 1987, 53, 5.807, 307.771, '1:34.200 (Max Verstappen, 2022)', 'Een klassiek en technisch circuit, beroemd om de achtvormige lay-out.', 'De iconische S-bochten en de 130R bocht zijn een ware test voor de coureurs.', 16, 2023, '2023-09-24 07:00:00'),
('losail_international_circuit', 'Losail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Qatar_Circuit.png.transform/content-image-full-width/image.png', 2021, 57, 5.419, 308.680, '1:24.319 (Max Verstappen, 2023)', 'Een snel en vloeiend circuit met een lange rechte stuk.', 'De nachtrace en de zanderige omgeving zorgen voor een uitdagende race.', 17, 2023, '2023-10-08 18:00:00'),
('circuit_of_the_americas', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/USA_Circuit.png.transform/content-image-full-width/image.png', 2012, 56, 5.513, 308.405, '1:37.387 (George Russell, 2023)', 'Een modern circuit met veel hoogteverschillen en een indrukwekkende eerste bocht.', 'De combinatie van snelle, vloeiende secties en krappe haarspeldbochten zorgt voor spectaculaire races.', 18, 2023, '2023-10-22 21:00:00'),
('autodromo_hermanos_rodriguez', 'Autodromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico City, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Mexico_Circuit.png.transform/content-image-full-width/image.png', 1963, 71, 4.304, 305.354, '1:17.771 (Valtteri Bottas, 2021)', 'Een circuit op grote hoogte, wat de aerodynamica en motorkoeling beïnvloedt.', 'Bekend om het stadiongedeelte, waar duizenden fans de coureurs aanmoedigen.', 19, 2023, '2023-10-29 20:00:00'),
('interlagos', 'Interlagos', 'São Paulo Grand Prix', 'São Paulo, Brazil', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Brazil_Circuit.png.transform/content-image-full-width/image.png', 1973, 71, 4.309, 305.909, '1:13.286 (Sergio Pérez, 2023)', 'Een historisch circuit met een spectaculair hoogteverschil en lange rechte stukken.', 'De ligging op een helling en de onvoorspelbare weersomstandigheden maken dit een klassieke race.', 20, 2023, '2023-11-05 17:00:00'),
('las_vegas_strip_circuit', 'Las Vegas Strip Circuit', 'Las Vegas Grand Prix', 'Las Vegas, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Las_Vegas_Circuit.png.transform/content-image-full-width/image.png', 2023, 50, 6.201, 310.000, '1:35.490 (Oscar Piastri, 2023)', 'Een nieuw stratencircuit over de beroemde Las Vegas Strip.', 'Een unieke nachtrace met hoge snelheden en een indrukwekkende achtergrond.', 21, 2023, '2023-11-19 07:00:00'),
('yas_marina_circuit', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, UAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Abu_Dhabi_Circuit.png.transform/content-image-full-width/image.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met spectaculaire architectuur, waaronder het Yas Hotel.', 'De traditionele seizoensafsluiter en de enige dag-tot-nachtrace op de kalender.', 22, 2023, '2023-11-26 14:00:00');

INSERT IGNORE INTO circuits (circuit_key, title, grandprix, location, map_url, first_gp_year, lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights, calendar_order, race_year, race_datetime) VALUES
('bahrain_international_circuit', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Bahrain_Circuit.png.transform/content-image-full-width/image.png', 2004, 57, 5.412, 308.238, '1:34.225 (Charles Leclerc, 2022)', 'De moderne seizoensopener onder kunstlicht.', 'Bekend om de spannende gevechten en een unieke mix van snelle en technische secties.', 1, 2022, '2022-03-20 16:00:00'),
('jeddah_corniche_circuit', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saudi Arabia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Saudi_Arabia_Circuit.png.transform/content-image-full-width/image.png', 2021, 50, 6.174, 308.450, '1:31.634 (Charles Leclerc, 2022)', 'Het snelste stratencircuit ter wereld, gelegen aan de Rode Zee.', 'Snelle, vloeiende bochten en nauwe passages, wat zorgt voor een hoge snelheid en weinig marge voor fouten.', 2, 2022, '2022-03-27 18:00:00'),
('albert_park_circuit', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Australia_Circuit.png.transform/content-image-full-width/image.png', 1996, 58, 5.278, 306.124, '1:20.672 (Charles Leclerc, 2022)', 'Een semi-stratencircuit rondom een meer in een stadspark.', 'Snel en vloeiend, met een aangepaste lay-out die meer inhaalmogelijkheden biedt.', 3, 2022, '2022-04-10 07:00:00'),
('autodromo_enzo_e_dino_ferrari', 'Autodromo Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Imola_Circuit.png.transform/content-image-full-width/image.png', 1980, 63, 4.909, 309.049, '1:18.446 (Max Verstappen, 2022)', 'Een klassiek Europees circuit met een rijke geschiedenis.', 'Een smal en technisch circuit met snelle chicanes en weinig inhaalmogelijkheden.', 4, 2022, '2022-04-24 15:00:00'),
('miami_international_autodrome', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Miami_Circuit.png.transform/content-image-full-width/image.png', 2022, 57, 5.412, 308.326, '1:31.361 (Max Verstappen, 2022)', 'Het debuut van dit stratencircuit rondom het Hard Rock Stadium.', 'De mix van snelle en langzame secties maakt het een uitdagend circuit voor de coureurs.', 5, 2022, '2022-05-08 21:30:00'),
('circuit_de_catalunya', 'Circuit de Catalunya', 'Spanish Grand Prix', 'Barcelona, Spain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Spain_Circuit.png.transform/content-image-full-width/image.png', 1991, 66, 4.657, 307.230, '1:24.167 (Sergio Pérez, 2022)', 'Een testcircuit dat bekend staat om zijn snelle chicanes en lange bochten.', 'Dit circuit wordt vaak gebruikt voor wintertests, dus de teams kennen het goed.', 6, 2022, '2022-05-22 15:00:00'),
('circuit_de_monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monte Carlo, Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Monaco_Circuit.png.transform/content-image-full-width/image.png', 1950, 78, 3.337, 260.286, '1:14.467 (Lando Norris, 2021)', 'Het meest iconische stratencircuit, waar de coureur het verschil maakt.', 'Een race van uithoudingsvermogen en precisie, met de beroemde tunnel en de Casino Square.', 7, 2022, '2022-05-29 15:00:00'),
('baku_city_circuit', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbaijan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Azerbaijan_Circuit.png.transform/content-image-full-width/image.png', 2016, 51, 6.003, 306.049, '1:46.046 (Charles Leclerc, 2022)', 'Een stratencircuit met een extreem lang recht stuk en een smalle, technische sectie.', 'Vaak vol met actie en safety cars vanwege de lastige bochten en hoge snelheden.', 8, 2022, '2022-06-12 13:00:00'),
('circuit_gilles_villeneuve', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Canada_Circuit.png.transform/content-image-full-width/image.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Een semi-stratencircuit op het Île Notre-Dame in Montreal.', 'Beroemd om de "Wall of Champions" en de onvoorspelbare weersomstandigheden.', 9, 2022, '2022-06-19 20:00:00'),
('silverstone_circuit', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Great Britain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Great_Britain_Circuit.png.transform/content-image-full-width/image.png', 1950, 52, 5.891, 306.198, '1:30.510 (Lewis Hamilton, 2022)', 'De thuisbasis van de Britse motorsport en een van de snelste circuits op de kalender.', 'Een klassiek circuit met legendarische bochten zoals Copse, Maggotts en Becketts.', 10, 2022, '2022-07-03 16:00:00'),
('red_bull_ring', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Austria', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Austria_Circuit.png.transform/content-image-full-width/image.png', 1970, 71, 4.318, 306.452, '1:07.275 (Sergio Pérez, 2020)', 'Een pittoresk circuit in de heuvels van Oostenrijk.', 'Kort en snel met veel hoogteverschillen, wat leidt tot spannende gevechten.', 11, 2022, '2022-07-10 15:00:00'),
('circuit_paul_ricard', 'Circuit Paul Ricard', 'French Grand Prix', 'Le Castellet, France', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/France_Circuit.png.transform/content-image-full-width/image.png', 1971, 53, 5.842, 309.692, '1:35.776 (Carlos Sainz, 2022)', 'Een modern circuit met lange rechte stukken en veel ontsnappingsruimte.', 'Bekend om zijn blauwe en rode strepen die de veiligheid verhogen en de grip beïnvloeden.', 12, 2022, '2022-07-24 15:00:00'),
('hungaroring', 'Hungaroring', 'Hungarian Grand Prix', 'Budapest, Hungary', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Hungary_Circuit.png.transform/content-image-full-width/image.png', 1986, 70, 4.381, 306.630, '1:21.386 (Lewis Hamilton, 2020)', 'Een krap en bochtig circuit waar inhalen erg moeilijk is.', 'Vaak vergeleken met Monaco, maar dan met meer ruimte en een hogere snelheid.', 13, 2022, '2022-07-31 15:00:00'),
('circuit_de_spa_francorchamps', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, Belgium', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Belgium_Circuit.png.transform/content-image-full-width/image.png', 1950, 44, 7.004, 308.052, '1:49.076 (Max Verstappen, 2022)', 'Een van de langste en meest geliefde circuits, met de beroemde Eau Rouge.', 'Bekend om zijn onvoorspelbare weer, dat vaak van het ene deel van het circuit verschilt van het andere.', 14, 2022, '2022-08-28 15:00:00'),
('circuit_zandvoort', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Netherlands', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Netherlands_Circuit.png.transform/content-image-full-width/image.png', 1952, 72, 4.259, 306.648, '1:13.652 (Max Verstappen, 2022)', 'Een compact en uitdagend duinencircuit met spectaculaire banking.', 'De sfeer is uniek met de oranje-gekleurde tribunes en het enthousiaste publiek.', 15, 2022, '2022-09-04 15:00:00'),
('autodromo_nazionale_monza', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Italy_Circuit.png.transform/content-image-full-width/image.png', 1950, 53, 5.793, 307.029, '1:24.030 (Sergio Pérez, 2022)', 'De "Temple of Speed", een historisch circuit met lange rechte stukken.', 'Bekend om de hoge snelheden en de beroemde Tifosi, de gepassioneerde Ferrari-fans.', 16, 2022, '2022-09-11 15:00:00'),
('marina_bay_street_circuit', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Marina Bay, Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Singapore_Circuit.png.transform/content-image-full-width/image.png', 2008, 61, 5.063, 308.828, '1:46.458 (George Russell, 2022)', 'De eerste nachtrace in de geschiedenis van de Formule 1.', 'De hoge temperaturen en luchtvochtigheid maken het een fysiek zware race voor de coureurs.', 17, 2022, '2022-10-02 14:00:00'),
('suzuka_international_racing_course', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Japan_Circuit.png.transform/content-image-full-width/image.png', 1987, 53, 5.807, 307.771, '1:34.200 (Max Verstappen, 2022)', 'Een klassiek en technisch circuit, beroemd om de achtvormige lay-out.', 'De iconische S-bochten en de 130R bocht zijn een ware test voor de coureurs.', 18, 2022, '2022-10-09 07:00:00'),
('circuit_of_the_americas', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/USA_Circuit.png.transform/content-image-full-width/image.png', 2012, 56, 5.513, 308.405, '1:38.788 (George Russell, 2022)', 'Een modern circuit met veel hoogteverschillen en een indrukwekkende eerste bocht.', 'De combinatie van snelle, vloeiende secties en krappe haarspeldbochten zorgt voor spectaculaire races.', 19, 2022, '2022-10-23 21:00:00'),
('autodromo_hermanos_rodriguez', 'Autodromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico City, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Mexico_Circuit.png.transform/content-image-full-width/image.png', 1963, 71, 4.304, 305.354, '1:18.423 (George Russell, 2022)', 'Een circuit op grote hoogte, wat de aerodynamica en motorkoeling beïnvloedt.', 'Bekend om het stadiongedeelte, waar duizenden fans de coureurs aanmoedigen.', 20, 2022, '2022-10-30 20:00:00'),
('interlagos', 'Interlagos', 'São Paulo Grand Prix', 'São Paulo, Brazil', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Brazil_Circuit.png.transform/content-image-full-width/image.png', 1973, 71, 4.309, 305.909, '1:13.882 (George Russell, 2022)', 'Een historisch circuit met een spectaculair hoogteverschil en lange rechte stukken.', 'De ligging op een helling en de onvoorspelbare weersomstandigheden maken dit een klassieke race.', 21, 2022, '2022-11-13 17:00:00'),
('yas_marina_circuit', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, UAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Abu_Dhabi_Circuit.png.transform/content-image-full-width/image.png', 2009, 58, 5.281, 306.183, '1:28.394 (Lando Norris, 2022)', 'Een modern circuit met spectaculaire architectuur.', 'De traditionele seizoensafsluiter en de enige dag-tot-nachtrace op de kalender.', 22, 2022, '2022-11-20 14:00:00');

INSERT IGNORE INTO circuits (circuit_key, title, grandprix, location, map_url, first_gp_year, lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights, calendar_order, race_year, race_datetime) VALUES
('bahrain_international_circuit', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Bahrain_Circuit.png.transform/content-image-full-width/image.png', 2004, 56, 5.412, 305.358, '1:32.740 (Lewis Hamilton, 2021)', 'De moderne seizoensopener onder kunstlicht.', 'Bekend om de spannende gevechten en een unieke mix van snelle en technische secties.', 1, 2021, '2021-03-28 17:00:00'),
('autodromo_enzo_e_dino_ferrari', 'Autodromo Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Imola_Circuit.png.transform/content-image-full-width/image.png', 1980, 63, 4.909, 309.049, '1:16.702 (Lewis Hamilton, 2020)', 'Een klassiek Europees circuit met een rijke geschiedenis.', 'Een smal en technisch circuit met snelle chicanes en weinig inhaalmogelijkheden.', 2, 2021, '2021-04-18 15:00:00'),
('autodromo_internacional_do_algarve', 'Autódromo Internacional do Algarve', 'Portuguese Grand Prix', 'Portimão, Portugal', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Portugal_Circuit.png.transform/content-image-full-width/image.png', 2020, 66, 4.653, 306.840, '1:18.750 (Lewis Hamilton, 2020)', 'Een modern circuit met veel hoogteverschillen in de Algarve.', 'De vloeiende lay-out en de "blinde" bochten maken het een uitdagend circuit voor coureurs.', 3, 2021, '2021-05-02 16:00:00'),
('circuit_de_catalunya', 'Circuit de Catalunya', 'Spanish Grand Prix', 'Barcelona, Spain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Spain_Circuit.png.transform/content-image-full-width/image.png', 1991, 66, 4.675, 308.424, '1:18.149 (Lewis Hamilton, 2021)', 'Een testcircuit dat bekend staat om zijn snelle chicanes en lange bochten.', 'Dit circuit wordt vaak gebruikt voor wintertests, dus de teams kennen het goed.', 4, 2021, '2021-05-09 15:00:00'),
('circuit_de_monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monte Carlo, Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Monaco_Circuit.png.transform/content-image-full-width/image.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest iconische stratencircuit, waar de coureur het verschil maakt.', 'Een race van uithoudingsvermogen en precisie, met de beroemde tunnel en de Casino Square.', 5, 2021, '2021-05-23 15:00:00'),
('baku_city_circuit', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbaijan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Azerbaijan_Circuit.png.transform/content-image-full-width/image.png', 2016, 51, 6.003, 306.049, '1:44.481 (Max Verstappen, 2021)', 'Een stratencircuit met een extreem lang recht stuk en een smalle, technische sectie.', 'Vaak vol met actie en safety cars vanwege de lastige bochten en hoge snelheden.', 6, 2021, '2021-06-06 13:00:00'),
('circuit_paul_ricard', 'Circuit Paul Ricard', 'French Grand Prix', 'Le Castellet, France', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/France_Circuit.png.transform/content-image-full-width/image.png', 1971, 53, 5.842, 309.692, '1:36.786 (Max Verstappen, 2021)', 'Een modern circuit met lange rechte stukken en veel ontsnappingsruimte.', 'Bekend om zijn blauwe en rode strepen die de veiligheid verhogen en de grip beïnvloeden.', 7, 2021, '2021-06-20 15:00:00'),
('red_bull_ring', 'Red Bull Ring', 'Styrian Grand Prix', 'Spielberg, Austria', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Austria_Circuit.png.transform/content-image-full-width/image.png', 2020, 71, 4.318, 306.452, '1:06.425 (Max Verstappen, 2021)', 'Een pittoresk circuit in de heuvels van Oostenrijk.', 'Kort en snel met veel hoogteverschillen, wat leidt tot spannende gevechten.', 8, 2021, '2021-06-27 15:00:00'),
('red_bull_ring', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Austria', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Austria_Circuit.png.transform/content-image-full-width/image.png', 1970, 71, 4.318, 306.452, '1:06.200 (Max Verstappen, 2021)', 'Een pittoresk circuit in de heuvels van Oostenrijk.', 'Kort en snel met veel hoogteverschillen, wat leidt tot spannende gevechten.', 9, 2021, '2021-07-04 15:00:00'),
('silverstone_circuit', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Great Britain', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Great_Britain_Circuit.png.transform/content-image-full-width/image.png', 1950, 52, 5.891, 306.198, '1:28.749 (Sergio Pérez, 2020)', 'De thuisbasis van de Britse motorsport en een van de snelste circuits op de kalender.', 'Een klassiek circuit met legendarische bochten zoals Copse, Maggotts en Becketts.', 10, 2021, '2021-07-18 16:00:00'),
('hungaroring', 'Hungaroring', 'Hungarian Grand Prix', 'Budapest, Hungary', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Hungary_Circuit.png.transform/content-image-full-width/image.png', 1986, 70, 4.381, 306.630, '1:18.394 (Pierre Gasly, 2021)', 'Een krap en bochtig circuit waar inhalen erg moeilijk is.', 'Vaak vergeleken met Monaco, maar dan met meer ruimte en een hogere snelheid.', 11, 2021, '2021-08-01 15:00:00'),
('circuit_de_spa_francorchamps', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, Belgium', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Belgium_Circuit.png.transform/content-image-full-width/image.png', 1950, 44, 7.004, 308.052, '1:46.286 (Valtteri Bottas, 2018)', 'Een van de langste en meest geliefde circuits, met de beroemde Eau Rouge.', 'Bekend om zijn onvoorspelbare weer, dat vaak van het ene deel van het circuit verschilt van het andere.', 12, 2021, '2021-08-29 15:00:00'),
('circuit_zandvoort', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Netherlands', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Netherlands_Circuit.png.transform/content-image-full-width/image.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een compact en uitdagend duinencircuit met spectaculaire banking.', 'De sfeer is uniek met de oranje-gekleurde tribunes en het enthousiaste publiek.', 13, 2021, '2021-09-05 15:00:00'),
('autodromo_nazionale_monza', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italy', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Italy_Circuit.png.transform/content-image-full-width/image.png', 1950, 53, 5.793, 307.029, '1:21.046 (Rubens Barrichello, 2004)', 'De "Temple of Speed", een historisch circuit met lange rechte stukken.', 'Bekend om de hoge snelheden en de beroemde Tifosi, de gepassioneerde Ferrari-fans.', 14, 2021, '2021-09-12 15:00:00'),
('autodromo_sochi', 'Sochi Autodrom', 'Russian Grand Prix', 'Sochi, Russia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Russia_Circuit.png.transform/content-image-full-width/image.png', 2014, 53, 5.848, 309.745, '1:35.761 (Lewis Hamilton, 2019)', 'Een modern stratencircuit rondom het Olympisch park van Sochi.', 'Bekend om de lange bochten en de uitdagingen voor de banden.', 15, 2021, '2021-09-26 14:00:00'),
('suzuka_international_racing_course', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Japan_Circuit.png.transform/content-image-full-width/image.png', 1987, 53, 5.807, 307.771, '1:34.200 (Max Verstappen, 2022)', 'Een klassiek en technisch circuit, beroemd om de achtvormige lay-out.', 'De iconische S-bochten en de 130R bocht zijn een ware test voor de coureurs.', 16, 2021, '2021-10-10 07:00:00'),
('circuit_of_the_americas', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, USA', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/USA_Circuit.png.transform/content-image-full-width/image.png', 2012, 56, 5.513, 308.405, '1:38.412 (Sergio Pérez, 2021)', 'Een modern circuit met veel hoogteverschillen en een indrukwekkende eerste bocht.', 'De combinatie van snelle, vloeiende secties en krappe haarspeldbochten zorgt voor spectaculaire races.', 17, 2021, '2021-10-24 21:00:00'),
('autodromo_hermanos_rodriguez', 'Autodromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico City, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Mexico_Circuit.png.transform/content-image-full-width/image.png', 1963, 71, 4.304, 305.354, '1:17.771 (Valtteri Bottas, 2021)', 'Een circuit op grote hoogte, wat de aerodynamica en motorkoeling beïnvloedt.', 'Bekend om het stadiongedeelte, waar duizenden fans de coureurs aanmoedigen.', 18, 2021, '2021-11-07 20:00:00'),
('interlagos', 'Interlagos', 'São Paulo Grand Prix', 'São Paulo, Brazil', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Brazil_Circuit.png.transform/content-image-full-width/image.png', 1973, 71, 4.309, 305.909, '1:11.010 (Valtteri Bottas, 2021)', 'Een historisch circuit met een spectaculair hoogteverschil en lange rechte stukken.', 'De ligging op een helling en de onvoorspelbare weersomstandigheden maken dit een klassieke race.', 19, 2021, '2021-11-14 17:00:00'),
('losail_international_circuit', 'Losail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Qatar_Circuit.png.transform/content-image-full-width/image.png', 2021, 57, 5.380, 306.660, '1:23.196 (Max Verstappen, 2021)', 'Een snel en vloeiend circuit met een lange rechte stuk.', 'De nachtrace en de zanderige omgeving zorgen voor een uitdagende race.', 20, 2021, '2021-11-21 15:00:00'),
('jeddah_corniche_circuit', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saudi Arabia', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Saudi_Arabia_Circuit.png.transform/content-image-full-width/image.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit ter wereld, gelegen aan de Rode Zee.', 'Snelle, vloeiende bochten en nauwe passages, wat zorgt voor een hoge snelheid en weinig marge voor fouten.', 21, 2021, '2021-12-05 18:00:00'),
('yas_marina_circuit', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, UAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%202023/Abu_Dhabi_Circuit.png.transform/content-image-full-width/image.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met spectaculaire architectuur.', 'De traditionele seizoensafsluiter en de enige dag-tot-nachtrace op de kalender.', 22, 2021, '2021-12-12 14:00:00');

INSERT INTO circuits (circuit_key, title, grandprix, location, map_url, first_gp_year, lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights, calendar_order, race_year, race_datetime) VALUES
('australia', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Australia_Circuit.png', 1996, 58, 5.278, 306.124, '1:19.813 (Charles Leclerc, 2024)', 'Een semi-stratencircuit rondom het Albert Park Lake, bekend om zijn snelle, vloeiende lay-out en uitdagende bochten. Het is vaak de seizoensopener.', 'Semi-stratencircuit, Snelle en vloeiende lay-out, Vaak seizoensopener.', 1, 2025, '2025-03-16 06:00:00'),
('china', 'Shanghai International Circuit', 'Chinese Grand Prix', 'Shanghai, China', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/China_Circuit.png', 2004, 56, 5.451, 305.066, '1:32.238 (Michael Schumacher, 2004)', 'Ontworpen met de vorm van het Chinese karakter "Shang" (boven/omhoog), staat bekend om zijn unieke bochtencombinaties en lange rechte stukken.', 'Unieke "Shang" vorm, Lange rechte stukken, Uitdagende bochtencombinaties.', 2, 2025, '2025-03-23 07:00:00'),
('japan', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Japan_Circuit.png', 1987, 53, 5.807, 307.471, '1:30.983 (Lewis Hamilton, 2019)', 'Een favoriet onder coureurs vanwege zijn unieke "achtbaan"-lay-out met een crossover. Bekend om zijn snelle, technische secties en de iconische 130R bocht.', 'Unieke crossover lay-out, Technische en snelle bochten, Iconische 130R.', 3, 2025, '2025-04-06 06:00:00'),
('bahrain', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrein', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Bahrain_Circuit.png', 2004, 57, 5.412, 308.238, '1:31.447 (Pedro de la Rosa, 2005)', 'Een modern circuit in de woestijn, vaak de openingsrace. Bekend om zijn races onder kunstlicht en de uitdaging van zand op de baan.', 'Nachtrace onder kunstlicht, Woestijnomgeving, Zand op de baan.', 4, 2025, '2025-04-13 14:00:00'),
('saudi_arabia', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saoedi-Arabië', 'https://www.formula1.com/content/dam/fom-website/2021/Saudi%20Arabia/jeddah-circuit-map.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit op de kalender, met veel blinde bochten en hoge snelheden langs de kustlijn.', 'Snelste stratencircuit, Veel blinde bochten, Hoge snelheden.', 5, 2025, '2025-04-20 17:00:00'),
('miami', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami Gardens, VS', 'https://www.formula1.com/content/dam/fom-website/2022/Miami/Miami_Circuit.png', 2022, 57, 5.412, 308.370, '1:29.708 (Max Verstappen, 2023)', 'Een speciaal gebouwd circuit rondom het Hard Rock Stadium, met een mix van snelle secties en krappe bochten, en een iconische "nep-haven".', 'Speciaal gebouwd circuit, Mix van snelle en krappe bochten, "Nep-haven" feature.', 6, 2025, '2025-05-04 19:30:00'),
('emilia_romagna', 'Autodromo Internazionale Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Imola_Circuit.png', 1980, 63, 4.909, 309.267, '1:15.484 (Lewis Hamilton, 2020)', 'Een historisch circuit met een ouderwetse lay-out, smal en uitdagend, bekend om zijn snelle chicanes en de Tamburello-bocht.', 'Historisch circuit, Smal en uitdagend, Snelle chicanes.', 7, 2025, '2025-05-18 13:00:00'),
('monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Monaco_Circuit.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest glamoureuze en veeleisende stratencircuit, waar precisie en moed essentieel zijn. Overtaken is hier extreem moeilijk.', 'Glamoureus stratencircuit, Zeer veeleisend, Overtaken is moeilijk.', 8, 2025, '2025-05-25 13:00:00'),
('spain', 'Circuit de Barcelona-Catalunya', 'Spanish Grand Prix', 'Barcelona, Spanje', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Spain_Circuit.png', 1991, 66, 4.675, 308.424, '1:18.149 (Max Verstappen, 2021)', 'Dit circuit is een vaste waarde op de kalender en staat bekend om zijn uitgebreide testmogelijkheden in het voorseizoen. Het biedt een mix van snelle en langzame bochten, wat het een goede indicator maakt voor de algemene prestaties van een auto. De lange laatste bocht is cruciaal voor de banden.', 'Vaak gebruikt voor wintertests, Mix van snelle en langzame secties, De laatste sector is cruciaal voor bandenmanagement.', 9, 2025, '2025-06-01 13:00:00'),
('canada', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Canada_Circuit.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Gelegen op een eiland in Montreal, bekend om zijn lange rechte stukken, krappe chicanes en de beruchte "Wall of Champions".', 'Gelegen op een eiland, Lange rechte stukken, "Wall of Champions".', 10, 2025, '2025-06-15 18:00:00'),
('austria', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Oostenrijk', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Austria_Circuit.png', 1970, 71, 4.318, 306.452, '1:05.619 (Carlos Sainz, 2020)', 'Een kort, snel en heuvelachtig circuit met veel hoogteverschillen, wat zorgt voor spectaculaire inhaalacties.', 'Kort en snel circuit, Veel hoogteverschillen, Spectaculaire inhaalacties.', 11, 2025, '2025-06-29 13:00:00'),
('great_britain', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Groot-Brittannië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Great_Britain_Circuit.png', 1950, 52, 5.891, 306.198, '1:27.097 (Max Verstappen, 2020)', 'De thuisbasis van de Britse motorsport, bekend om zijn snelle, vloeiende secties zoals Maggotts, Becketts en Chapel.', 'Historisch circuit, Snelle en vloeiende secties, Iconische bochtencombinaties.', 12, 2025, '2025-07-06 14:00:00'),
('belgium', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, België', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Belgium_Circuit.png', 1950, 44, 7.004, 308.052, '1:46.286 (Valtteri Bottas, 2018)', 'Een van de meest legendarische en langste circuits, bekend om zijn snelle, vloeiende lay-out, hoogteverschillen en de iconische Eau Rouge-Raidillon combinatie.', 'Langste circuit op de kalender, Veel hoogteverschillen, Iconische Eau Rouge.', 13, 2025, '2025-07-27 13:00:00'),
('hungary', 'Hungaroring', 'Hungarian Grand Prix', 'Mogyoród, Hongarije', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Hungary_Circuit.png', 1986, 70, 4.381, 306.670, '1:16.627 (Lewis Hamilton, 2020)', 'Een bochtig circuit dat vaak wordt vergeleken met een kartbaan, waar inhalen lastig is en de nadruk ligt op aerodynamische grip.', 'Bochtig en technisch, Moeilijk om in te halen, Hoge aerodynamische grip vereist.', 14, 2025, '2025-08-03 13:00:00'),
('netherlands', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Nederland', 'https://www.formula1.com/content/dam/fom-website/2020/Netherlands/Zandvoort_Circuit.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een klassiek circuit met duinen en banking bochten, bekend om zijn uitdagende lay-out en de enthousiaste Nederlandse fans.', 'Circuit in de duinen, Banking bochten, Enthousiaste fans.', 15, 2025, '2025-08-31 13:00:00'),
('italy', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Italy_Circuit.png', 1950, 53, 5.793, 306.720, '1:21.046 (Rubens Barrichello, 2004)', 'De "Temple of Speed", bekend om zijn lange rechte stukken en snelle chicanes, waar topsnelheid cruciaal is.', 'De "Temple of Speed", Lange rechte stukken, Snelle chicanes.', 16, 2025, '2025-09-07 13:00:00'),
('azerbaijan', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbeidzjan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Azerbaijan_Circuit.png', 2016, 51, 6.003, 306.049, '1:43.009 (Charles Leclerc, 2019)', 'Een stratencircuit met een mix van extreem lange rechte stukken en een zeer smal, technisch gedeelte rondom het kasteel.', 'Stratencircuit, Extreem lange rechte stukken, Smal kasteelgedeelte.', 17, 2025, '2025-09-21 11:00:00'),
('singapore', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Singapore_Circuit.png', 2008, 62, 5.063, 313.870, '1:35.867 (Lewis Hamilton, 2018)', 'Het eerste nachtrace-circuit in de F1-geschiedenis, bekend om zijn hoge luchtvochtigheid, hobbelige oppervlak en fysiek veeleisende lay-out.', 'Eerste nachtrace, Hoge luchtvochtigheid, Fysiek veeleisend.', 18, 2025, '2025-10-05 12:00:00'),
('usa', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, VS', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/USA_Circuit.png', 2012, 56, 5.513, 308.405, '1:36.169 (Charles Leclerc, 2019)', 'Een modern circuit met een unieke mix van secties geïnspireerd op andere beroemde circuits, inclusief een steile klim naar bocht 1.', 'Moderne lay-out, Geïnspireerd op andere circuits, Steile klim naar bocht 1.', 19, 2025, '2025-10-19 19:00:00'),
('mexico', 'Autódromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico-Stad, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Mexico_Circuit.png', 1963, 71, 4.304, 305.354, '1:17.774 (Valtteri Bottas, 2021)', 'Gelegen op grote hoogte, wat de motoren en aerodynamica uitdaagt. Bekend om de sfeervolle Foro Sol-stadionsectie.', 'Hoge hoogte, Uitdaging voor motoren/aerodynamica, Foro Sol stadion.', 20, 2025, '2025-10-26 19:00:00'),
('brazil', 'Autódromo José Carlos Pace', 'São Paulo Grand Prix', 'São Paulo, Brazilië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Brazil_Circuit.png', 1973, 71, 4.309, 305.909, '1:10.540 (Valtteri Bottas, 2018)', 'Een historisch circuit met een korte, intense lay-out en veel hoogteverschillen, vaak het toneel van dramatische races.', 'Historisch circuit, Korte en intense lay-out, Dramatische races.', 21, 2025, '2025-11-09 16:00:00'),
('las_vegas', 'Las Vegas Strip Circuit', 'Las Vegas Grand Prix', 'Paradise, VS', 'https://www.formula1.com/content/dam/fom-website/2023/Las%20Vegas/Las_Vegas_Circuit.png', 2023, 50, 6.201, 309.958, '1:34.876 (Lando Norris, 2024)', 'Een nieuw stratencircuit dat over de beroemde Las Vegas Strip loopt, bekend om zijn hoge snelheden en de spectaculaire nachtelijke setting.', 'Nieuw stratencircuit, Over de Las Vegas Strip, Spectaculaire nachtrace.', 22, 2025, '2025-11-22 06:00:00'),
('qatar', 'Lusail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2023/Qatar/Qatar_Circuit.png', 2021, 57, 5.419, 308.611, '1:22.384 (Lando Norris, 2024)', 'Een modern circuit in de woestijn, bekend om zijn snelle, vloeiende lay-out en de race onder kunstlicht.', 'Modern woestijncircuit, Snelle en vloeiende lay-out, Nachtrace.', 23, 2025, '2025-11-30 14:00:00'),
('abu_dhabi', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, VAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Abu_Dhabi_Circuit.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met indrukwekkende faciliteiten, bekend om zijn jachthaven en de race die begint bij zonsondergang en eindigt in het donker.', 'Moderne faciliteiten, Jachthaven setting, Zonsondergang naar nachtrace.', 24, 2025, '2025-12-07 13:00:00');