<script type="application/json" id="animalsRegistryJson">{!! \App\Support\AnimalRegistry::json(fn ($q) => $q->where('status', 'active')) !!}</script>
