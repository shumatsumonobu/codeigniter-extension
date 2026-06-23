# Sandbox

Standalone scripts for manually exercising library features. Most of them need
the dependencies installed first:

```sh
composer install
```

## Scripts

| Script | Description |
| --- | --- |
| `rekognition-detect-faces.php` | Amazon Rekognition face detection. |
| `rekognition-compare-faces.php` | Amazon Rekognition face comparison. |
| `gif-extract-first-frame.php` | Extract the first frame of an animated GIF (Imagick). |
| `gif-count-frames.php` | Count the number of frames in an animated GIF (Imagick). |
| `file-get-owner.php` | Print the owner, group, and permissions of a file. |
| `form-validation-test.php` | CodeIgniter 3 form validation test (checkbox array validation). |
| `rest-client.php` | REST client test (pairs with `rest-server.php`). |
| `rest-server.php` | Minimal REST server for `rest-client.php` (run with `php -S`). |

## Amazon Rekognition

`rekognition-*.php` exercise `\X\Rekognition\Client` against the real AWS
Rekognition API.

### Authentication

Credentials are **optional**. Omit them to authenticate via the AWS SDK default
provider chain (e.g. EC2 instance profile / IAM role). Pass them as CLI
arguments to use an explicit IAM user access key instead.

```sh
# IAM role (EC2 instance profile) — no credentials
php sandbox/rekognition-detect-faces.php

# Explicit IAM user access key
php sandbox/rekognition-detect-faces.php <ACCESS_KEY> <SECRET_KEY>
```

### Face detection — `rekognition-detect-faces.php`

Detects faces in `input/person_a.png` and prints the count and details.

```sh
php sandbox/rekognition-detect-faces.php [<ACCESS_KEY> <SECRET_KEY>]
```

### Face comparison — `rekognition-compare-faces.php`

Compares the same person (`person_a.png` vs `person_a_alt.png`) and two
different people (`person_a.png` vs `person_b.png`), printing the similarity
percentage for each. Expect a high value for the same person and a low value
for different people.

```sh
php sandbox/rekognition-compare-faces.php [<ACCESS_KEY> <SECRET_KEY>]
```

## GIF helpers

### Extract first frame — `gif-extract-first-frame.php`

Reads `input/sample-animated.gif`, extracts the first frame, and writes it to
`output/sample_0.gif`.

```sh
php sandbox/gif-extract-first-frame.php
```

### Count frames — `gif-count-frames.php`

Prints the number of frames in `input/sample-animated.gif`.

```sh
php sandbox/gif-count-frames.php
```

## File info — `file-get-owner.php`

Prints the owner (UID), group (GID), and permission bits of a file.

```sh
php sandbox/file-get-owner.php
```

## Form validation — `form-validation-test.php`

Bootstraps CodeIgniter 3 from `demo/` and runs a form validation test
(checkbox array validation). Requires the demo dependencies to be installed.

```sh
php sandbox/form-validation-test.php
```

## REST client/server — `rest-client.php` / `rest-server.php`

`rest-client.php` exercises `\X\Util\RestClient` against `rest-server.php`, a
minimal endpoint that echoes the request headers (logged to `output/`).

```sh
# Terminal 1 — start the server (serves the sandbox/ directory)
cd sandbox && php -S localhost:9000

# Terminal 2 — run the client
php sandbox/rest-client.php
```

## Test images (`input/`)

| File | Used by | Description |
| --- | --- | --- |
| `person_a.png` | rekognition-* | Person A |
| `person_a_alt.png` | rekognition-compare-faces | Person A, a different photo |
| `person_b.png` | rekognition-compare-faces | Person B |
| `sample-animated.gif` | gif-* | Multi-frame animated GIF |
