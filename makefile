.PHONY: provision

provision:
	docker-compose exec wordpress wp --allow-root plugin uninstall --deactivate hello.php akismet || true
	docker-compose exec wordpress wp --allow-root plugin install --activate gutenberg || true
