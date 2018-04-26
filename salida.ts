console.log("Hello world");

//Types
var myString : string = "Hello world";
var myNumber : number = 22;
var myBool : boolean = true;

//Strings
document.write(myString);

//Arrays
var StringArray: any[] = ["hola",2,true];
var numberArray: number[] = [1,2,3];

//Tupple
var strNumTupple: [string, number];
strNumTupple = [ "hola",22 ];

//void, undefind, null
var myVoid: void = undefined;

let mySum = function(
    num1: number | string, 
    num2: number | string):number {
        
        if(typeof(num1) === 'string'){
            num1 = parseInt(num1);
        }
        
        if(typeof(num2) === 'string'){
            num2 = parseInt(num2);
        }

        return num1 + num2;
    }

    class testClass{

        public nombre = "";
        public apellido = "";

        public __contruct(nombre,apellido){
            this.nombre = nombre;
            this.apellido = apellido;
        }

        public saludar(){
            return "Hola "+this.nombreCompleto();
        }

        private nombreCompleto(){
            return this.nombre+" "+this.apellido;
        }
    }