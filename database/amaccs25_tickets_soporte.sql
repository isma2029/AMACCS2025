-- Eliminar la base de datos existente si existe
DROP DATABASE IF EXISTS amaccs25;

-- Crear la base de datos AMACCS25
CREATE DATABASE amaccs25;

-- Usar la base de datos
USE amaccs25;

-- Tabla de Usuarios (Técnicos y personal de soporte)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    departamento VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    rol ENUM('administrador', 'tecnico', 'soporte', 'usuario') DEFAULT 'usuario'
);

-- Tabla de Departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    responsable_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id_usuario)
);

-- Tabla de Categorías de Equipos
CREATE TABLE IF NOT EXISTS categorias_equipos (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Equipos
CREATE TABLE IF NOT EXISTS equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    id_departamento INT,
    nombre VARCHAR(200) NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    numero_serie VARCHAR(100) UNIQUE,
    fecha_compra DATE,
    garantia_hasta DATE,
    estado ENUM('disponible', 'en_mantenimiento', 'dañado', 'baja') DEFAULT 'disponible',
    especificaciones_tecnicas TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_equipos(id_categoria),
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento)
);

-- Tabla de Tickets
CREATE TABLE IF NOT EXISTS tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    id_equipo INT,
    id_usuario_solicitante INT NOT NULL,
    id_tecnico_asignado INT,
    id_departamento INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_cierre DATETIME,
    prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    estado ENUM('abierto', 'en_progreso', 'en_espera', 'resuelto', 'cerrado', 'cancelado') DEFAULT 'abierto',
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo),
    FOREIGN KEY (id_usuario_solicitante) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_tecnico_asignado) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento)
);

-- Tabla de Seguimientos de Tickets
CREATE TABLE IF NOT EXISTS seguimientos_ticket (
    id_seguimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_usuario INT NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('comentario', 'cambio_estado', 'asignacion', 'cierre', 'reabierto') NOT NULL,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabla de Piezas/Repuestos
CREATE TABLE IF NOT EXISTS piezas (
    id_pieza INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    cantidad_disponible INT DEFAULT 0,
    cantidad_minima INT DEFAULT 5,
    proveedor VARCHAR(200),
    numero_parte VARCHAR(100),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de Piezas utilizadas en Tickets
CREATE TABLE IF NOT EXISTS piezas_utilizadas (
    id_pieza_utilizada INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_pieza INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    descripcion_uso TEXT,
    fecha_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket),
    FOREIGN KEY (id_pieza) REFERENCES piezas(id_pieza)
);

-- Insertar usuario administrador por defecto (contraseña: admin123)
INSERT INTO usuarios (nombre, apellido, email, contrasena, rol) 
VALUES ('Administrador', 'Sistema', 'admin@amaccs25.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');

-- Insertar categorías de equipos comunes
INSERT INTO categorias_equipos (nombre, descripcion) VALUES
('Computadora de escritorio', 'Equipos de cómputo de escritorio'),
('Laptop', 'Computadoras portátiles'),
('Impresora', 'Equipos de impresión'),
('Servidor', 'Servidores y equipos de red'),
('Monitor', 'Monitores y pantallas'),
('Dispositivo móvil', 'Tablets y teléfonos inteligentes'),
('Periférico', 'Teclados, ratones, etc.'),
('Red', 'Equipos de red y comunicaciones');

-- Insertar departamentos de ejemplo
INSERT INTO departamentos (nombre, descripcion) VALUES
('Sistemas', 'Departamento de Tecnologías de la Información'),
('Recursos Humanos', 'Departamento de Recursos Humanos'),
('Contabilidad', 'Departamento de Contabilidad y Finanzas'),
('Ventas', 'Departamento de Ventas'),
('Soporte Técnico', 'Equipo de soporte técnico');
