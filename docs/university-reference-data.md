# University offices, colleges & fund sources

Reference rows for `department_units` and `fund_sources` are defined in:

- `database/data/university_reference_data.php`

They are loaded with:

```bash
php artisan db:seed --class=UniversityReferenceDataSeeder
```

**Funds only** (does not create or change `department_units`; departments must already exist with the same `name` as in the data file):

```bash
php artisan db:seed --class=FundSourcesOnlySeeder
# or:
php artisan db:seed --class=FundSources
```

## Behaviour

- **Upserts** departments by `name` (updates `abbreviation` only; does not change existing `budget`).
- **New** departments get `budget = 0.00` when your schema requires `budget` to be NOT NULL.
- **Upserts** fund sources by `(department_unit_id, name)`.
- **Removes** `fund_sources` rows for each department defined in the file whose `name` is no longer listed for that department (obsolete funds). If the database refuses deletes because of foreign keys, clear dependent rows first or run `php artisan university:reset-fund-sources --force` (see command help).
- Departments that are **removed from the file** are left in the database (and their funds are not pruned by this seeder). Use `university:reset-fund-sources --reset-departments` if you need a full department reset.

## Defaults

- Fund names are stored **as written** in the data file (unique per department via `(department_unit_id, name)`).
- Offices without a dedicated fund list get **`General / Operating Fund`**.

## Naming notes

- **Colleges** use the format **`College of …(ABBR)`** (no space before the parenthesis), e.g. **College of Engineering(COE)**. **`and`** is spelled out where applicable (not `&`). **CPAD** is stored as **College of Public Administration(CPAD)** (no “& Governance” in the label). **Architecture** uses abbreviation **CARCH**. Seeder renames older spaced/`&` names on sync.
- **National Service Training Porgram Office** matches the source document spelling; rows named **National Service Training Program Office** are renamed on seed.
- **Vice President for Finance and Administration (VPFAD)** (GASS, “and”) and **Vice President for Finance & Administration (VPFAD)** (Support, “&”) are two distinct `department_units`.
- **Quality Management Office- ISO** matches the source list; rows named **Quality Management Office - ISO** are renamed on seed.

To change the master list, edit `database/data/university_reference_data.php` and run the seeder again.
