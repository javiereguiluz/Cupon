/**
 * Crea un contador de tiempo que se actualiza cada segundo y que muestra
 * cuánto falta para que expire la oferta.
 */
function muestraCuentaAtras(id, fecha) {
    var horas, minutos, segundos;
    
    var ahora = new Date();
    var fechaExpiracion = new Date(fecha.ano, fecha.mes, fecha.dia, fecha.hora, fecha.minuto, fecha.segundo);
    
    var falta = Math.floor( (fechaExpiracion.getTime() - ahora.getTime()) / 1000 );
    
    if (falta < 0) {
        cuentaAtras = '-';
    }
    else {
        horas = Math.floor(falta/3600);
        falta = falta % 3600;
        
        minutos = Math.floor(falta/60);
        falta = falta % 60;
        
        segundos = Math.floor(falta);
        
        cuentaAtras = (horas < 10    ? '0' + horas    : horas)    + 'h '
                    + (minutos < 10  ? '0' + minutos  : minutos)  + 'm '
                    + (segundos < 10 ? '0' + segundos : segundos) + 's ';
        
        setTimeout(function() {
            muestraCuentaAtras(id, fecha);
        }, 1000);
    }
    
    document.getElementById(id).innerHTML = cuentaAtras;
}