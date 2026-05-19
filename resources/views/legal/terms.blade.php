@extends('layouts.site')

@section('title', 'Términos y condiciones | lapsique.media')

@section('content')
    <section class="bg-[var(--beige-100)] py-24 sm:py-28">
        <div class="mx-auto max-w-4xl px-6">
            <div class="card overflow-hidden">
                <div class="border-b border-[var(--beige-300)] bg-[var(--beige-50)] px-6 py-8 sm:px-10">
                    <p class="label-small text-[var(--marine-500)]">Información legal</p>
                    <h1 class="mt-2 display text-3xl font-semibold text-[var(--ink)] sm:text-4xl">Términos y condiciones</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--muted)]">
                        Estos términos regulan la compra, registro y uso de accesos para eventos comercializados en lapsique.media.
                    </p>
                </div>

                <div class="space-y-8 px-6 py-8 text-sm leading-7 text-[var(--muted)] sm:px-10">
                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">1. Aceptación</h2>
                        <p>
                            Al registrarte, reservar o comprar un acceso a través de este sitio aceptas estos términos y condiciones,
                            así como las reglas particulares publicadas para cada evento.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">2. Naturaleza del acceso</h2>
                        <p>
                            Todo acceso emitido para un evento es personal e intransferible. El organizador podrá solicitar identificación oficial
                            y negar el acceso cuando detecte uso compartido, reventa, cesión a terceros o cualquier inconsistencia en el registro.
                        </p>
                        <p>
                            Cada QR, código o confirmación digital corresponde exclusivamente a la persona registrada y sólo podrá usarse conforme
                            a las condiciones del producto adquirido.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">3. Pagos y cargos</h2>
                        <p>
                            Los precios, cargos por servicio, capacidad, horarios y beneficios incluidos se muestran durante el proceso de compra.
                            En los productos consumibles, el monto indicado para consumo se utilizará dentro del evento conforme a la dinámica publicada.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">4. Política de cancelación y reembolsos</h2>
                        <p>
                            Salvo que exista una disposición legal aplicable en sentido distinto, los accesos y reservaciones son no reembolsables.
                            Una vez confirmada la compra, no se realizan devoluciones parciales o totales por inasistencia, cambios personales de plan,
                            error del comprador o falta de uso del acceso.
                        </p>
                        <p>
                            En caso de cancelación oficial del evento o cambio sustancial determinado por el organizador, cualquier alternativa aplicable
                            se comunicará por los canales oficiales.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">5. Registro y datos del asistente</h2>
                        <p>
                            El comprador es responsable de proporcionar datos completos y veraces. Si un acceso requiere registro individual,
                            deberá capturarse la información de cada asistente antes del ingreso o dentro del plazo indicado por la plataforma.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">6. Admisión y permanencia</h2>
                        <p>
                            El acceso al evento está sujeto a aforo, horarios, revisión de seguridad, políticas internas del venue y lineamientos
                            de conducta. El organizador podrá restringir o revocar la entrada por razones de seguridad, incumplimiento de reglas,
                            comportamiento violento o cualquier situación que ponga en riesgo la operación del evento.
                        </p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold text-[var(--ink)]">7. Modificaciones</h2>
                        <p>
                            El organizador puede actualizar estos términos y condiciones en cualquier momento. La versión publicada en este sitio será la vigente
                            para nuevas compras y registros.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-[var(--marine-200)] bg-[var(--marine-50)] px-5 py-4 text-[var(--marine-800)]">
                        <p class="font-semibold">Resumen clave</p>
                        <p class="mt-1">
                            Los accesos son personales, intransferibles y no reembolsables.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </section>
@endsection
