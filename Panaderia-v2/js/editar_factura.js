/*  =====================================================
    JAVASCRIPT PARA LA FECHA DE COBRO
    ===================================================== */
const estado = document.getElementById("estado");
const fechaCobro = document.getElementById("fecha_cobro");

function actualizarFechaCobro() {
    if (!estado || !fechaCobro) {
        return;
    }

    if (estado.value === "Cobrado") {
        fechaCobro.disabled = false;

    } else {
        fechaCobro.disabled = true;
        fechaCobro.value = "";
    }
}

if (estado) {
    estado.addEventListener(
        "change",
        actualizarFechaCobro
    );
    actualizarFechaCobro();
}
