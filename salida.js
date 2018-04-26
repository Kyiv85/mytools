console.log("Hello world");
//Types
var myString = "Hello world";
var myNumber = 22;
var myBool = true;
//Strings
document.write(myString);
//Arrays
var StringArray = ["hola", 2, true];
var numberArray = [1, 2, 3];
//Tupple
var strNumTupple;
strNumTupple = ["hola", 22];
//void, undefind, null
var myVoid = undefined;
var mySum = function (num1, num2) {
    if (typeof (num1) === 'string') {
        num1 = parseInt(num1);
    }
    if (typeof (num2) === 'string') {
        num2 = parseInt(num2);
    }
    return num1 + num2;
};
var testClass = /** @class */ (function () {
    function testClass() {
        this.nombre = "";
        this.apellido = "";
    }
    testClass.prototype.__contruct = function (nombre, apellido) {
        this.nombre = nombre;
        this.apellido = apellido;
    };
    testClass.prototype.saludar = function () {
        return "Hola " + this.nombreCompleto();
    };
    testClass.prototype.nombreCompleto = function () {
        return this.nombre + " " + this.apellido;
    };
    return testClass;
}());
