Earthquake Science Center (ESC) Seminars
========================================

Web application for [ESC's weekly seminar series](https://earthquake.usgs.gov/contactus/menlo/seminars/) that displays seminar details for upcoming and past seminars, including videos and podcast feeds. The application also sends automated emails announcing upcoming seminars to the current distribution list.

## Installation

First install [Node.js](https://nodejs.org/) and [Grunt](https://gruntjs.com).

**Note**: You will also need PHP with CGI installed.

1. Clone project

```
git clone https://github.com/shaefner-usgs/earthquake-seminars.git
```

2. Install dependencies

```
cd earthquake-seminars
npm install

# If you need to add a CA certificate file:
npm config set cafile "<path to your certificate file>"

# Check the 'cafile'
npm config get cafile
```

3. Configure app

```
cd earthquake-seminars/src/lib

# Run configuration script and accept defaults
./pre-install
```

4. Run grunt

```
cd earthquake-seminars
grunt
```
