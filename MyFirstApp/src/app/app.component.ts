import { Component } from '@angular/core';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  public title : string;
  public name : string;
  public hobbies : string[];
  public sw : boolean;

  constructor(){
    this.title = "THISISTHETITLE";
    this.name = "Daniel Eduardo";
    this.hobbies = [ 'Run','Read','Develop','Internet'];
    this.sw = true;
  }

  public showHobbies(){
    window.setInterval(this.changeBool(), 2000);
  }

  private changeBool(){
    if (this.sw){
      this.sw=false;
    }
    else{
      this.sw=true;
    }
    return this.sw;
  }

  
}
