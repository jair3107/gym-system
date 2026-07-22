<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Nueva venta
        </h2>
    </x-slot>

    <div class="py-10" x-data="ventaPOS()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('ventas._tabs')

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('ventas.store') }}">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    {{-- Catálogo --}}
                    <div class="lg:col-span-3 card p-6 space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="t in tipos" :key="t.key">
                                <button type="button" @click="tipo = t.key"
                                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                                        :class="tipo === t.key
                                            ? 'bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20'
                                            : 'text-zinc-400 hover:text-white hover:bg-white/5'"
                                        x-text="t.label"></button>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="item in catalogo[tipo]" :key="item.id">
                                <button type="button" @click="agregar(tipo, item)"
                                        class="text-left p-4 rounded-lg border border-white/[0.06] bg-ink-800/60 hover:border-gold-400/30 hover:bg-white/[0.03] transition disabled:opacity-40 disabled:cursor-not-allowed"
                                        :disabled="item.stock !== null && restante(tipo, item) <= 0">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-medium text-zinc-200" x-text="item.nombre"></span>
                                        <span class="text-gold-300 font-semibold whitespace-nowrap" x-text="'$' + fmt(item.precio)"></span>
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-500">
                                        <span x-show="item.extra" x-text="item.extra"></span>
                                        <span x-show="item.stock !== null" x-text="'Stock: ' + restante(tipo, item)"></span>
                                    </div>
                                </button>
                            </template>
                            <p x-show="catalogo[tipo].length === 0" class="text-sm text-zinc-500 sm:col-span-2 py-6 text-center">
                                No hay elementos disponibles en este catálogo.
                            </p>
                        </div>
                    </div>

                    {{-- Carrito / cobro --}}
                    <div class="lg:col-span-2 card p-6 space-y-4">
                        <h3 class="section-title">Ticket</h3>

                        <div class="space-y-2 min-h-24">
                            <template x-for="(item, idx) in carrito" :key="item.tipo + '-' + item.id">
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-ink-800/60 border border-white/[0.05]">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-zinc-200 truncate" x-text="item.nombre"></div>
                                        <div class="text-xs text-zinc-500" x-text="'$' + fmt(item.precio) + ' c/u'"></div>
                                    </div>
                                    <div class="flex items-center gap-1" x-show="item.tipo !== 'membresia'">
                                        <button type="button" @click="decrementar(idx)"
                                                class="h-6 w-6 rounded bg-white/5 text-zinc-300 hover:bg-white/10">−</button>
                                        <span class="w-6 text-center text-sm text-zinc-200" x-text="item.cantidad"></span>
                                        <button type="button" @click="incrementar(idx)"
                                                class="h-6 w-6 rounded bg-white/5 text-zinc-300 hover:bg-white/10 disabled:opacity-30"
                                                :disabled="item.stock !== null && item.cantidad >= item.stock">+</button>
                                    </div>
                                    <div class="w-16 text-right text-sm text-zinc-100 font-medium" x-text="'$' + fmt(item.precio * item.cantidad)"></div>
                                    <button type="button" @click="quitar(idx)" class="text-zinc-500 hover:text-red-400 text-lg leading-none">&times;</button>

                                    <input type="hidden" :name="`items[${idx}][tipo]`" :value="item.tipo">
                                    <input type="hidden" :name="`items[${idx}][id]`" :value="item.id">
                                    <input type="hidden" :name="`items[${idx}][cantidad]`" :value="item.cantidad">
                                </div>
                            </template>
                            <p x-show="carrito.length === 0" class="text-sm text-zinc-500 py-6 text-center">
                                Toca un elemento del catálogo para agregarlo.
                            </p>
                        </div>

                        <div class="flex items-center justify-between border-t border-white/[0.06] pt-3">
                            <span class="text-zinc-400">Total</span>
                            <span class="text-2xl font-brand font-bold text-gold-300" x-text="'$' + fmt(total)"></span>
                        </div>

                        <div>
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select id="cliente_id" name="cliente_id" x-model="clienteId" class="select mt-1 w-full">
                                <option value="">— Mostrador (sin cliente) —</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                            <p x-show="requiereCliente && !clienteId" class="text-xs text-amber-400 mt-1">
                                Las membresías requieren seleccionar un cliente.
                            </p>
                        </div>

                        <div x-show="puedeAutoRenovar" class="rounded-lg border border-gold-400/20 bg-gold-400/[0.06] p-3">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" x-model="autoRenovacion"
                                       class="mt-0.5 rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30">
                                <span class="text-sm text-zinc-300">
                                    Activar <span class="text-gold-300 font-medium">auto-renovación</span> (suscripción)
                                    <span class="block text-xs text-zinc-500">Se hará autocobro a la tarjeta primaria del cliente al vencer y se le avisará.</span>
                                </span>
                            </label>
                        </div>
                        <div x-show="hayMembresiaRenovable && requiereCliente && clienteId && !clienteTienePrimaria"
                             class="rounded-lg border border-amber-500/20 bg-amber-500/[0.06] p-3 text-xs text-amber-400">
                            El cliente no tiene tarjeta primaria, así que no se puede activar la auto-renovación.
                            Agrégale una desde su ficha.
                        </div>
                        <input type="hidden" name="auto_renovacion" :value="(autoRenovacion && puedeAutoRenovar) ? 1 : 0">

                        <div>
                            <x-input-label for="metodo_pago" value="Método de pago" />
                            <select id="metodo_pago" name="metodo_pago" class="select mt-1 w-full" required>
                                @foreach ($metodos as $metodo)
                                    <option value="{{ $metodo->value }}" @selected(old('metodo_pago') === $metodo->value)>
                                        {{ $metodo->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="notas" value="Notas (opcional)" />
                            <textarea id="notas" name="notas" rows="2" class="textarea mt-1 block w-full">{{ old('notas') }}</textarea>
                        </div>

                        <button type="submit" class="btn-gold w-full justify-center"
                                :disabled="carrito.length === 0 || (requiereCliente && !clienteId)"
                                :class="(carrito.length === 0 || (requiereCliente && !clienteId)) ? 'opacity-40 cursor-not-allowed' : ''">
                            Cobrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ventaPOS() {
            return {
                tipo: 'membresia',
                tipos: [
                    { key: 'membresia', label: 'Membresías' },
                    { key: 'producto', label: 'Productos' },
                    { key: 'servicio', label: 'Servicios' },
                ],
                catalogo: @json($catalogo),
                carrito: [],
                clienteId: '',
                autoRenovacion: false,
                clientesConPrimaria: @json($clientesConPrimaria),
                get total() {
                    return this.carrito.reduce((sum, i) => sum + i.precio * i.cantidad, 0);
                },
                get requiereCliente() {
                    return this.carrito.some(i => i.tipo === 'membresia');
                },
                get hayMembresiaRenovable() {
                    return this.carrito.some(i => i.tipo === 'membresia' && i.renovable);
                },
                get clienteTienePrimaria() {
                    return this.clienteId !== '' && this.clientesConPrimaria.includes(Number(this.clienteId));
                },
                get puedeAutoRenovar() {
                    return this.hayMembresiaRenovable && this.clienteTienePrimaria;
                },
                fmt(n) {
                    return Number(n).toFixed(2);
                },
                enCarrito(tipo, item) {
                    return this.carrito.find(i => i.tipo === tipo && i.id === item.id);
                },
                restante(tipo, item) {
                    if (item.stock === null) return null;
                    const linea = this.enCarrito(tipo, item);
                    return item.stock - (linea ? linea.cantidad : 0);
                },
                agregar(tipo, item) {
                    const existente = this.enCarrito(tipo, item);
                    if (tipo === 'membresia') {
                        if (existente) return; // una vigencia por plan
                        this.carrito.push({ tipo, id: item.id, nombre: item.nombre, precio: item.precio, cantidad: 1, stock: null, renovable: !!item.renovable });
                        return;
                    }
                    if (existente) {
                        if (item.stock !== null && existente.cantidad >= item.stock) return;
                        existente.cantidad++;
                        return;
                    }
                    this.carrito.push({ tipo, id: item.id, nombre: item.nombre, precio: item.precio, cantidad: 1, stock: item.stock });
                },
                incrementar(idx) {
                    const i = this.carrito[idx];
                    if (i.stock !== null && i.cantidad >= i.stock) return;
                    i.cantidad++;
                },
                decrementar(idx) {
                    const i = this.carrito[idx];
                    if (i.cantidad <= 1) { this.quitar(idx); return; }
                    i.cantidad--;
                },
                quitar(idx) {
                    this.carrito.splice(idx, 1);
                },
            };
        }
    </script>
</x-app-layout>
