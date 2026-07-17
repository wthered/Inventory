<?php

	namespace App\Services\Stock;

	use App\Enums\Inventory\AdjustmentReason;
	use Exception;

	class AdjustmentReasonService {
		/**
		 * Generate complete HTML select dropdown for adjustment reasons
		 *
		 * @throws Exception
		 */
		public function generateReasonDropdown(?string $selectedValue = null, array $attributes = []): string {
			$reasons = AdjustmentReason::forDropdown()->toArray();

			// Build select attributes
			$selectAttributes = array_merge([
				'name'     => 'reason',
				'id'       => 'adjustReason',
				'class'    => 'form-control',
				'required' => 'required',
			], $attributes);

			$attributesHtml = $this->buildAttributes($selectAttributes);

			// Start building HTML
			$html = "<select $attributesHtml>\n";
			$html .= "    <option value=''>Select a reason</option>\n";

			foreach ($reasons as $group => $options) {
				if (empty($options)) {
					continue;
				}

				$html .= "    <optgroup label=\"{$group}\">\n";

				foreach ($options as $value => $label) {
					$reason = AdjustmentReason::from($value);

					$selected      = $selectedValue === $value ? 'selected' : '';
					$color         = htmlspecialchars($reason->color());
					$icon          = htmlspecialchars($reason->icon());
					$requiresNotes = $reason->requiresNotes();
					$requiresBatch = $reason->requiresBatch();

					$html .= "        <option value='".$value."' ".$selected." data-color='" . $color . "' data-icon='" . $icon . "' data-requires-notes='" . $requiresNotes . "' data-requires-batch='" . $requiresBatch . "'>" . $label . "</option>\n";
				}
				$html .= "    </optgroup>\n";
			}

			$html .= "</select>";

			return $html;
		}

		private function buildAttributes(array $attributes): string {
			$html = [];

			foreach ($attributes as $key => $value) {
				if (is_bool($value)) {
					if ($value) {
						$html[] = $key;
					}
				} else {
					$html[] = $key . '="' . htmlspecialchars($value) . '"';
				}
			}

			return implode(' ', $html);
		}
	}