<?php
/**
 * Application base model.
 *
 * All application models should extend this class.
 * Inherits query builder methods and CRUD operations from \X\Model\Model.
 *
 * @example Basic model usage
 * ```php
 * class ProductModel extends AppModel {
 *   const TABLE = 'product';
 *
 *   public function getActiveProducts(): array {
 *     return $this->where('status', 'active')->get_all();
 *   }
 * }
 * ```
 */
abstract class AppModel extends \X\Model\Model {}