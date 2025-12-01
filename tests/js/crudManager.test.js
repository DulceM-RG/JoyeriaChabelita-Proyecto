import { jest } from "@jest/globals";
import CrudManager from "../../src/js/crudManager";
import { expect } from "expect";

//Nombre representativo para las pruebas
describe("CrudManager", () => {
  beforeEach(() => {
    global.fetch = jest.fn();
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe("createData", () => {
    test("debería crear datos exitosamente cuando la respuesta es válida", async () => {
      // Preparación - Configurar datos de prueba y mock
      const crudManager = new CrudManager("http://localhost/api/");
      const mockResponse = { success: true, id: 1 };
      global.fetch.mockResolvedValue({
        ok: true,
        headers: { get: () => "application/json" },
        json: () => Promise.resolve(mockResponse)
      });

      // Ejecución - Llamar al método bajo prueba
      const result = await crudManager.createData("usuarios", { nombre: "Juan" }, "createUser");

      // Verificación - Comprobar resultados esperados
      expect(fetch).toHaveBeenCalledWith("http://localhost/api/createUser.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ datosFormulario: { nombre: "Juan" }, tabla: "usuarios" })
      });
      expect(result).toEqual(mockResponse);
    });

    test("debería manejar errores HTTP devolviendo respuesta JSON del servidor", async () => {
      // Preparación - Configurar respuesta de error del servidor
      const crudManager = new CrudManager("http://localhost/api/");
      const mockErrorResponse = { errorDB: "Usuario ya existe" };
      global.fetch.mockResolvedValue({
        ok: false,
        status: 400,
        headers: { get: () => "application/json" },
        json: () => Promise.resolve(mockErrorResponse)
      });

      // Ejecución - Llamar al método con datos que causan error
      const result = await crudManager.createData("usuarios", { email: "duplicado@test.com" }, "createUser");

      // Verificación - Comprobar que se devuelve el error del servidor
      expect(result).toEqual(mockErrorResponse);
    });

    test("debería manejar respuestas no-JSON del servidor", async () => {
      // Preparación - Configurar respuesta de texto plano
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockResolvedValue({
        ok: false,
        status: 500,
        headers: { get: () => "text/html" },
        text: () => Promise.resolve("Error interno del servidor PHP")
      });

      // Ejecución - Llamar al método cuando el servidor devuelve HTML/texto
      const result = await crudManager.createData("usuarios", { nombre: "Test" }, "createUser");

      // Verificación - Comprobar manejo de error de texto
      expect(result.errorServer).toContain("Error del servidor (500)");
      expect(result.errorServer).toContain("Error interno del servidor PHP");
    });

    test("debería manejar errores de conexión de red", async () => {
      // Preparación - Configurar error de red
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockRejectedValue(new Error("Network error"));

      // Ejecución - Llamar al método cuando hay error de conexión
      const result = await crudManager.createData("usuarios", { nombre: "Test" }, "createUser");

      // Verificación - Comprobar manejo de error de conexión
      expect(result).toEqual({
        errorServer: "Error de conexión: Network error"
      });
    });
  });

  describe("readAllData", () => {
    test("debería obtener todos los datos exitosamente", async () => {
      // Preparación - Configurar datos de respuesta simulados
      const crudManager = new CrudManager("http://localhost/api/");
      const mockData = [{ id: 1, nombre: "Juan" }, { id: 2, nombre: "María" }];
      global.fetch.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(mockData)
      });

      // Ejecución - Llamar al método para obtener datos
      const result = await crudManager.readAllData("usuarios");

      // Verificación - Comprobar llamada correcta y datos devueltos
      expect(fetch).toHaveBeenCalledWith("http://localhost/api/readAllData.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tabla: "usuarios" })
      });
      expect(result).toEqual(mockData);
    });

    test("debería manejar errores HTTP al obtener datos", async () => {
      // Preparación - Configurar respuesta de error HTTP
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockResolvedValue({
        ok: false,
        status: 404
      });

      // Ejecución - Llamar al método cuando hay error HTTP
      const result = await crudManager.readAllData("usuarios");

      // Verificación - Comprobar manejo de error HTTP
      expect(result).toEqual({
        errorServer: "Error al obtener los datos: Error del servidor HTTP: 404"
      });
    });

    test("debería manejar errores de conexión al obtener datos", async () => {
      // Preparación - Configurar error de red
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockRejectedValue(new Error("Connection timeout"));

      // Ejecución - Llamar al método cuando hay timeout
      const result = await crudManager.readAllData("usuarios");

      // Verificación - Comprobar manejo de error de conexión
      expect(result).toEqual({
        errorServer: "Error al obtener los datos: Connection timeout"
      });
    });
  });

  describe("updateData", () => {
    test("debería actualizar datos exitosamente", async () => {
      // Preparación - Configurar datos de actualización y respuesta
      const crudManager = new CrudManager("http://localhost/api/");
      const datosActualizacion = { id: 1, nombre: "Juan Actualizado" };
      const mockResponse = { mensaje: "Actualización exitosa" };
      global.fetch.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(mockResponse)
      });

      // Ejecución - Llamar al método de actualización
      const result = await crudManager.updateData("usuarios", datosActualizacion);

      // Verificación - Comprobar llamada correcta y respuesta
      expect(fetch).toHaveBeenCalledWith("http://localhost/api/updateCredenciales.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ datosFormulario: datosActualizacion, tabla: "usuarios" })
      });
      expect(result).toEqual(mockResponse);
    });

    test("debería manejar errores al actualizar datos", async () => {
      // Preparación - Configurar error de servidor
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockResolvedValue({
        ok: false,
        status: 500
      });

      // Ejecución - Llamar al método cuando hay error del servidor
      const result = await crudManager.updateData("usuarios", { id: 1 });

      // Verificación - Comprobar manejo de error
      expect(result).toEqual({
        errorServer: "Error al actualizar los datos: Error del servidor HTTP: 500"
      });
    });
  });

  describe("deleteData", () => {
    test("debería eliminar datos exitosamente", async () => {
      // Preparación - Configurar identificador y respuesta de eliminación
      const crudManager = new CrudManager("http://localhost/api/");
      const identificador = { id: 1 };
      const mockResponse = { mensaje: "Eliminación exitosa" };
      global.fetch.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(mockResponse)
      });

      // Ejecución - Llamar al método de eliminación
      const result = await crudManager.deleteData("usuarios", identificador);

      // Verificación - Comprobar llamada correcta y respuesta
      expect(fetch).toHaveBeenCalledWith("http://localhost/api/deleteData.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ datosIdentificador: identificador, tabla: "usuarios" })
      });
      expect(result).toEqual(mockResponse);
    });

    test("debería manejar errores de conexión al eliminar datos", async () => {
      // Preparación - Configurar error de red
      const crudManager = new CrudManager("http://localhost/api/");
      global.fetch.mockRejectedValue(new Error("Server unreachable"));

      // Ejecución - Llamar al método cuando el servidor no es accesible
      const result = await crudManager.deleteData("usuarios", { id: 1 });

      // Verificación - Comprobar manejo de error de conexión
      expect(result).toEqual({
        errorServer: "Error al eliminar los datos: Server unreachable"
      });
    });
  });
});
