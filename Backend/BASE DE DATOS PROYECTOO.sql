DROP	 DATABASE IF EXISTS salon_eventos_syl; 

-- 1. Crear y seleccionar la base de datos
CREATE DATABASE IF NOT EXISTS salon_eventos_syl DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salon_eventos_syl;

-- 2. Tabla CLIENTE
CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni_cuit VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(30),
    contrasena VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 3. Tabla USUARIO (Personal administrativo / Operadores del sistema(nosotros))
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni_cuit VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(30),
    email VARCHAR(150) NOT NULL UNIQUE,
    rol VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- 4. Tabla SALON (Con soporte para las 3 imágenes y tipo de salón)
CREATE TABLE salon (
    id_salon INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    capacidad_maxima INT NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    precio_base DECIMAL(10,2) NOT NULL,
    foto_principal VARCHAR(255),
    foto_interior VARCHAR(255),
    foto_exterior VARCHAR(255),
    tipo_salon VARCHAR(50),
    estado VARCHAR(30) DEFAULT 'activo'
) ENGINE=InnoDB;

-- 5. Tabla PERSONAL (Empleados de servicio del salón)
CREATE TABLE personal (
    id_personal INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni_cuit VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(30),
    email VARCHAR(150),
    tipo_personal VARCHAR(50)
) ENGINE=InnoDB;

-- 6. Tabla PROVEEDOR (Para servicios externos)
CREATE TABLE proveedor (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    razon_social VARCHAR(150) NOT NULL,
    rubro VARCHAR(100)
) ENGINE=InnoDB;

-- 7. Tabla SERVICIO (Catálogo de servicios adicionales)
CREATE TABLE servicio (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre_servicio VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50)
) ENGINE=InnoDB;

-- 8. Tabla RESERVA (Relacionada con cliente, salón y usuario)
CREATE TABLE reserva (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_salon INT NOT NULL,
    id_usuario INT, -- Usuario que registró la reserva (puede ser nulo si reserva el cliente online)
    fecha_evento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    cantidad_invitados INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(30) DEFAULT 'Pendiente',
    temporada VARCHAR(50),
    CONSTRAINT fk_reserva_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    CONSTRAINT fk_reserva_salon FOREIGN KEY (id_salon) REFERENCES salon(id_salon),
    CONSTRAINT fk_reserva_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- 9. Tabla ASIGNACIÓN (Relaciona personal con reservas)
CREATE TABLE asignacion (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    id_personal INT NOT NULL,
    rol VARCHAR(50),
    horas DECIMAL(5,2),
    CONSTRAINT fk_asignacion_reserva FOREIGN KEY (id_reserva) REFERENCES reserva(id_reserva),
    CONSTRAINT fk_asignacion_personal FOREIGN KEY (id_personal) REFERENCES personal(id_personal)
) ENGINE=InnoDB;

-- 10. Tabla DETALLE_RESERVA_SERVICIO (Tabla intermedia para servicios incluidos en la reserva)
CREATE TABLE detalle_reserva_servicio (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    id_servicio INT NOT NULL,
    id_proveedor INT, -- Proveedor opcional que ofrece el servicio
    cantidad INT DEFAULT 1,
    precio_final DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_reserva FOREIGN KEY (id_reserva) REFERENCES reserva(id_reserva),
    CONSTRAINT fk_detalle_servicio FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio),
    CONSTRAINT fk_detalle_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
) ENGINE=InnoDB;

-- 11. Tabla PAGO (Registra los pagos asociados a una reserva)
CREATE TABLE pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME NOT NULL,
    medio_pago VARCHAR(50),
    concepto VARCHAR(100),
    metodo_pago VARCHAR(50) NOT NULL DEFAULT 'efectivo',
    estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
    CONSTRAINT fk_pago_reserva FOREIGN KEY (id_reserva) REFERENCES reserva(id_reserva)
) ENGINE=InnoDB;

-- Insertar Salones Reales del Proyecto
INSERT INTO salon (nombre, capacidad_maxima, direccion, precio_base, foto_principal, foto_interior, foto_exterior, tipo_salon, estado) VALUES
('Salón Imperial', 350, 'Av. Libertador 1234', 180000.00, 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1000', 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=1000', 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1000', 'Principal', 'activo'),
('Casita de fiesta', 70, 'Calle Ficticia 456', 250000.00, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000', 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?w=1000', 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=1000', 'Infantil / Cumpleaños', 'activo'),
('Quincho Campestre', 150, 'Zona Quintas 789', 140000.00, 'https://images.unsplash.com/photo-1533105079780-92b9be482077?w=1000', 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?w=1000', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000', 'Campestre', 'activo');

-- Insertar Servicios Reales (con sus precios correspondientes)
INSERT INTO servicio (nombre_servicio, descripcion, precio_base, categoria) VALUES
('DJ y Sonido', 'Equipamiento profesional e iluminación robótica', 50000.00, 'Entretenimiento'),
('Catering / Menú', 'Menú completo por invitado', 85000.00, 'Gastronomía'),
('Decoración y Ambientación', 'Arreglos florales y ornamentación temática', 50000.00, 'Estética'),
('Seguridad y Limpieza', 'Personal de control y mantenimiento', 50000.00, 'Operativa'),
('Fotografía y Video', 'Cobertura completa del evento', 50000.00, 'Multimedia'),
('Barra Libre / Coctelería', 'Servicio de barra y bebidas', 50000.00, 'Gastronomía');

USE salon_eventos_syl;

INSERT INTO personal (nombre, apellido, dni_cuit, telefono, email, tipo_personal) VALUES
-- Personal para Salón 1 (Salón Imperial)
('Carlos', 'Gómez', '20-33445566-9', '2914556677', 'carlos.gomez@email.com', 'Parrillero/Cocinero'),
('Mariana', 'López', '27-22334455-4', '2914889900', 'mariana.lopez@email.com', 'Tecnica en sonido y luces'),

-- Personal para Salón 2 (La Casita de Fiesta)
('Esteban', 'Pérez', '20-40556677-2', '2914112233', 'esteban.perez@email.com', 'Animador'),
('Lucía', 'Martínez', '27-35667788-1', '2914223344', 'lucia.martinez@email.com', 'Encargada de Recepción y Logística'),

-- Personal para Salón 3 (El Quincho)
('Diego', 'Fernández', '20-38990011-5', '2914667788', 'diego.fernandez@email.com', 'Mozos y Atención General'),
('Sofia', 'Ramírez', '27-41223344-6', '2914778899', 'sofia.ramirez@email.com', 'Coordinadora de Salón Quincho');


USE salon_eventos_syl;

-- Actualizar las 3 imágenes para el Salón Imperial (id_salon = 1)
UPDATE salon 
SET 
    foto_principal = 'img/salon1_FT_PRI.jpeg',
    foto_interior  = 'img/salon1_FT_INT.jpeg',
    foto_exterior  = 'img/salon1_FT_EXT.jpeg'
WHERE id_salon = 1;


USE salon_eventos_syl;

-- Insertar el usuario administrador fijo en la tabla 'usuario'
INSERT INTO usuario (nombre, apellido, dni_cuit, telefono, email, rol, contrasena) 
VALUES (
    'Administrador', 
    'General', 
    '20-11111111-9', 
    '2915000000', 
    'adminchad@gmail.com', 
    'Administrador', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- Hash bcrypt correspondiente a 'admin123'
);

-- 1. Borramos el administrador anterior por si quedó con otro hash
DELETE FROM usuario WHERE email = 'adminchad@gmail.com';

-- 2. Lo insertamos de nuevo con el hash exacto para 'admin123'
INSERT INTO usuario (nombre, apellido, dni_cuit, telefono, email, rol, contrasena) 
VALUES (
    'Administrador', 
    'General', 
    '20-11111111-9', 
    '2915000000', 
    'adminchad@gmail.com', 
    'Administrador', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);


