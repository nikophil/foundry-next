# 1.x to 2.x

* Bundle configuration has been updated _*_
* Factories:
  * Proxies are now instances of the object and must be created manually with the `proxy()` global function _*_
  * Have your persistent factories extend `PersistentObjectFactory` instead of `ModelFactory` _*_
  * Have your non-persistent factories (ie _embeddables_) extend `ObjectFactory` instead of `ModelFactory` _*_
  * Rename ObjectFactory's `protected static function getClass()` to `public static function class()` _*_
  * Rename ObjectFactory's `protected static function getDefaults()` to `protected static function defaults()` _*_

_* Can be added to 1.x for a smooth migration_
