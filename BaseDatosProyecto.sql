-- Crear base de datos
CREATE DATABASE HotelDB;

-- Usar la base de datos creada
USE HotelDB;

-- Tabla de Usuarios (anteriormente Clientes)
CREATE TABLE Usuarios (
    ID_usuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Apellido VARCHAR(100),
    Email VARCHAR(100) UNIQUE,
    Telefono VARCHAR(15),
    Direccion VARCHAR(255),
    Admin ENUM('Si', 'No') DEFAULT 'No',
    Contrasena VARCHAR(255) -- Almacenara la contrasena de forma segura
);

-- Tabla de Habitaciones
CREATE TABLE Habitaciones (
    ID_habitacion INT AUTO_INCREMENT PRIMARY KEY,
    Tipo VARCHAR(50),
    Precio_noche DECIMAL(10, 2),
    Estado ENUM('Disponible', 'Ocupada', 'Reservada'),
    Numero_camas INT
);

-- Tabla de Reservas
CREATE TABLE Reservas (
    ID_reserva INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT,
    ID_habitacion INT,
    Fecha_check_in DATE,
    Fecha_check_out DATE,
    Estado_reserva ENUM('Confirmada', 'Cancelada', 'Pendiente'),
    FOREIGN KEY (ID_usuario) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (ID_habitacion) REFERENCES Habitaciones(ID_habitacion)
);

-- Tabla de Servicios
CREATE TABLE Servicios (
    ID_servicio INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Descripcion TEXT,
    Precio DECIMAL(10, 2)
);

-- Tabla de Servicios_Reservas
CREATE TABLE Servicios_Reservas (
    ID_reserva INT,
    ID_servicio INT,
    Cantidad INT,
    PRIMARY KEY (ID_reserva, ID_servicio),
    FOREIGN KEY (ID_reserva) REFERENCES Reservas(ID_reserva),
    FOREIGN KEY (ID_servicio) REFERENCES Servicios(ID_servicio)
);
ALTER TABLE Reservas ADD COLUMN Total_reserva DECIMAL(10, 2);

-- Tabla de Trabajadores
CREATE TABLE Trabajadores (
    ID_trabajador INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Apellido VARCHAR(100),
    Cargo VARCHAR(100),
    Telefono VARCHAR(15),
    Fecha_ingreso DATE,
    Salario DECIMAL(10, 2)
);

-- Insertar Usuarios (con contrasenas generadas)
INSERT INTO Usuarios (Nombre, Apellido, Email, Telefono, Direccion, Admin, Contrasena) VALUES
('Pedro', 'Gonzalez', 'pedro@email.com', '5553456789', 'Calle 10, Piso 2, Madrid', 'No', 'PedroGonzalez123!'),
('Laura', 'Ramirez', 'laura@email.com', '5556789012', 'Avenida del Sol 234, Barcelona', 'No', 'LauraRamirez456$'),
('Luis', 'Sanchez', 'luis@email.com', '5559876540', 'Calle Luna 99, Valencia', 'No', 'LuisSanchez789#'),
('Ana', 'Martinez', 'ana@email.com', '5551122334', 'Calle del Mar 76, Malaga', 'No', 'AnaMartinez321@'),
('Javier', 'Moreno', 'javier@email.com', '5552233445', 'Calle Primavera 12, Sevilla', 'No', 'JavierMoreno654%');

INSERT INTO Usuarios (Nombre, Apellido, Email, Telefono, Direccion, Admin, Contraseña)
VALUES ('Carlos', 'Pérez', 'carlosadmin@email.com', '5559876543', 'Avenida Central 123, Madrid', 'Sí', 'CarlosAdmin2025!');


-- Insertar Habitaciones
INSERT INTO Habitaciones (Tipo, Precio_noche, Estado, Numero_camas) VALUES
('Individual', 55.00, 'Disponible', 1),
('Doble', 90.00, 'Disponible', 2),
('Doble', 85.00, 'Ocupada', 2),
('Triple', 120.00, 'Disponible', 3),
('Suite', 200.00, 'Ocupada', 2),
('Suite', 180.00, 'Disponible', 2),
('Cuadruple', 150.00, 'Disponible', 4),
('Individual', 60.00, 'Ocupada', 1),
('Doble', 95.00, 'Reservada', 2),
('Suite', 250.00, 'Disponible', 2);

-- Insertar Servicios
INSERT INTO Servicios (Nombre, Descripcion, Precio) VALUES
('Wi-Fi', 'Conexion a internet en todo el hotel', 5.00),
('Desayuno', 'Buffet libre para el desayuno', 12.00),
('Spa', 'Masajes y tratamientos de relajacion', 60.00),
('Gimnasio', 'Acceso al gimnasio durante la estancia', 15.00),
('Cena especial', 'Menu gourmet para la cena', 30.00),
('Parking', 'Estacionamiento en el hotel', 10.00),
('Traslado al aeropuerto', 'Servicio de transporte privado al aeropuerto', 40.00);

-- Insertar Trabajadores
INSERT INTO Trabajadores (Nombre, Apellido, Cargo, Telefono, Fecha_ingreso, Salario) VALUES
('Carlos', 'Gomez', 'Recepcionista', '5551234567', '2023-01-15', 1200.00),
('Ana', 'Martinez', 'Camarera', '5559876543', '2024-02-10', 900.00),
('Marta', 'Fernandez', 'Cocinera', '5555632498', '2022-08-01', 1400.00),
('David', 'Lopez', 'Mantenimiento', '5556724890', '2021-06-23', 1100.00),
('Sofia', 'Castro', 'Gerente', '5553142569', '2020-11-30', 2200.00),
('Luis', 'Perez', 'Camarero', '5559871254', '2024-01-18', 950.00);

-- Insertar Reservas
INSERT INTO Reservas (ID_usuario, ID_habitacion, Fecha_check_in, Fecha_check_out, Estado_reserva) VALUES
(1, 1, '2025-03-10', '2025-03-12', 'Confirmada'),
(2, 2, '2025-03-15', '2025-03-18', 'Confirmada'),
(3, 3, '2025-03-17', '2025-03-19', 'Confirmada'),
(4, 5, '2025-03-20', '2025-03-22', 'Confirmada'),
(5, 7, '2025-03-25', '2025-03-28', 'Confirmada'),
(1, 6, '2025-04-01', '2025-04-03', 'Pendiente'),
(3, 8, '2025-04-10', '2025-04-12', 'Pendiente');

-- Insertar Servicios_Reservas
INSERT INTO Servicios_Reservas (ID_reserva, ID_servicio, Cantidad) VALUES
(1, 1, 2), -- Wi-Fi para la reserva 1
(1, 2, 1), -- Desayuno para la reserva 1
(2, 3, 1), -- Spa para la reserva 2
(2, 6, 1), -- Parking para la reserva 2
(3, 5, 1), -- Cena especial para la reserva 3
(4, 1, 2), -- Wi-Fi para la reserva 4
(4, 4, 1), -- Gimnasio para la reserva 4
(5, 7, 1), -- Traslado al aeropuerto para la reserva 5
(6, 2, 1); -- Desayuno para la reserva 6
