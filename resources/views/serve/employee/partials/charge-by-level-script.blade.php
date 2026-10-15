<script>
(function () {
	var chargesByLevel = @json($chargesByLevel);
	var levelSelect = document.getElementById('level_id');
	var chargeSelect = document.getElementById('charge_id');

	if (!levelSelect || !chargeSelect) {
		return;
	}

	var selectedCharge = chargeSelect.getAttribute('data-selected') || '';

	function option(value, text, isSelected) {
		var el = document.createElement('option');
		el.value = value;
		el.textContent = text;
		if (isSelected) {
			el.selected = true;
		}
		return el;
	}

	function fillCharges(keepSelected) {
		var levelId = String(levelSelect.value || '');
		var current = keepSelected ? selectedCharge : '';

		chargeSelect.innerHTML = '';

		if (!levelId) {
			chargeSelect.appendChild(option('', 'Elija primero un nivel', true));
			chargeSelect.disabled = true;
			return;
		}

		var items = chargesByLevel[levelId] || [];
		if (!items.length) {
			chargeSelect.appendChild(option('', 'No hay cargos para este nivel', true));
			chargeSelect.disabled = true;
			return;
		}

		chargeSelect.disabled = false;
		chargeSelect.appendChild(option('', 'elija el cargo', current === ''));

		items.forEach(function (item) {
			chargeSelect.appendChild(option(
				item.id,
				item.name,
				String(item.id) === String(current)
			));
		});
	}

	levelSelect.addEventListener('change', function () {
		selectedCharge = '';
		fillCharges(false);
	});

	fillCharges(true);
})();
</script>
