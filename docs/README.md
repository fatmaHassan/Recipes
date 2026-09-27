# Documentation

## user-guide
This directory contains user guide documentation for the Recipes website organized by topic.
**Dita based documentations** is found under user-guide folder

to run the output install Dita OT  and give as an input the desired output format and the path to the dita map as follows

`/[ path to Dita OT ]/bin/dita --input=docs/user-guide/dita/maps/user-guide.ditamap --format=html5 --output=docs/user-guide/dita/output`

## Testing

- [Test Plan](testing/TEST_PLAN.md) - Comprehensive test strategy, coverage, and execution plan
- [Test Cases](testing/TEST_CASES.md) - Detailed test cases organized by feature area
- [Test Plan — Jira](testing/TEST_PLAN_JIRA.md) - Test plan for Jira / test management (cycles, smoke, E2E, performance & accessibility planned); use with `test-cases-export.csv` for import

## Structure

```
docs/
├── README.md (this file)
└── testing/ (Dokumentation related to tests)
    ├── TEST_PLAN.md
    ├── TEST_CASES.md
    ├── TEST_PLAN_JIRA.md
    ├── test-cases-export.csv
    └── test-cases/ (here the test cases)
└── user-guide/ ( User Guide dcumentation)
    ├── dita
        ├── maps
        ├── topics
        ├── output (the dita output is gitignored)



