var data_1;
var data_2;

function ofc_ready(){

}

function open_flash_chart_data()
{
    return JSON.stringify(data_1);
}

function load_1()
{
    tmp = findSWF("my_chart");
    x = tmp.load( JSON.stringify(data_1) );
}

function load_2()
{
    //alert("loading data_2");
    tmp = findSWF("my_chart");
    x = tmp.load( JSON.stringify(data_2) );
}

function findSWF(movieName) {
    if (navigator.appName.indexOf("Microsoft")!= -1) {
        return window[movieName];
    } else {
        return document[movieName];
    }
}