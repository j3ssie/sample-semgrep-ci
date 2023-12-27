# Github Action Example with Semgrep SAST

This repository contains an example workflow showcasing the integration of Semgrep, a powerful static analysis tool, within a GitHub Actions workflow for performing Static Application Security Testing (SAST).

## Workflow Overview

The provided GitHub Actions workflow demonstrates how to:

- Run Semgrep scan on your codebase.
- Save the scan results in SARIF format.
- Upload the SARIF file as an artifact.
- Utilize the GitHub `upload-sarif` action to display scan findings in the GitHub Security tab.

## Contents

- `.github/workflows/`: Contains the workflow YAML file.
- `vulnerable-source-code/`: Placeholder directory representing the codebase for scanning.
- `README.md`: Instructions and overview.

Feel free to use this as a reference for integrating Semgrep scans into your CI/CD pipelines and enhancing your code security.

## Manual Usage

```bash
semgrep scan -j 100 --config p/default --config ./custom-semgrep-rules/ src > out.txts

# with sarif format
semgrep scan -j 100 -q --sarif --config p/default --config ./custom-semgrep-rules/ src > semgrep-result.sarif

semgrep scan -j 100 -q --sarif --config p/default --config ./custom-semgrep-rules/ src > semgrep-result.sarif
```

> Tips: Using [SARIF Viewer](https://marketplace.visualstudio.com/items?itemName=MS-SarifVSCode.sarif-viewer) in VSCode or [sarif-tools](https://github.com/microsoft/sarif-tools) to beautify the sarif format file

## Github Action File

```yaml
# Name of this GitHub Actions workflow.
name: Semgrep

on:
  # Scan changed files in PRs (diff-aware scanning):
  pull_request: {}
  # Scan on-demand through GitHub Actions interface:
  workflow_dispatch: {}
  # Scan mainline branches and report all findings:
  push:
    branches: ["master", "main"]

jobs:
  semgrep_scan:
    # User definable name of this GitHub Actions job.
    name: semgrep/ci
    # If you are self-hosting, change the following `runs-on` value:
    runs-on: ubuntu-latest
    container:
      # A Docker image with Semgrep installed. Do not change this.
      image: returntocorp/semgrep
    # Skip any PR created by dependabot to avoid permission issues:
    if: (github.actor != 'dependabot[bot]')
    permissions:
      # required for all workflows
      security-events: write
      # only required for workflows in private repositories
      actions: read
      contents: read

    steps:
      # Fetch project source with GitHub Actions Checkout.
      - name: Checkout repository
        uses: actions/checkout@v3

      - name: Perform Semgrep Analysis
      # @NOTE: This is the actual semgrep command to scan your code.
      # Modify the --config option to 'r/all' to scan using all rules,
      # or use multiple flags to specify particular rules, such as
      # --config r/all --config custom/rules
        run: semgrep scan -q --sarif --config auto ./vulnerable-source-code > semgrep-results.sarif

      # upload the results for the CodeQL GitHub app to annotate the code
      - name: Save SARIF results as artifact
        uses: actions/upload-artifact@v3
        with:
          name: semgrep-scan-results
          path: semgrep-results.sarif

      # Upload SARIF file generated in previous step
      - name: Upload SARIF result to the GitHub Security Dashboard
        uses: github/codeql-action/upload-sarif@v2
        with:
          sarif_file: semgrep-results.sarif
        if: always()

```