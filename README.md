# zenstruck/foundry

## Contributing

Run `make help` to see all available operations.

### Running the Test Suite

> [!NOTE]
> Docker and PHP installed locally (with `pgsql` & `mongodb` extensions) is required.

```bash
# start docker
docker compose up -d

# run test suite with all available permutations
make test
```
