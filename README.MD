# Jikan REST API Source

This REST API is built on Lumen and is in development, therefore neither will I produce or help in any form for any kind of in-depth installation/set up or guides for the time being


## What this requires
- Composer
- Lumen
- BugSnag (API Key)
- JikanPHP Library (just composer update)
- Redis

## Quick set up
1. Git Clone the repo
2. Rename `.env.example` to `.env`
3. `composer update`
4. Make a BugSnag Account & set the API Key in `.env`
5. Install Redis

## For Local Development:

The project is using docker containers to maintain a stability between local and deployed version.


Installing Development Pre-Requisites
-------------------------------------

Install [Docker](https://www.docker.com).


You will also want to ensure that `./bin` is in your `$PATH` and is the highest priority. 
This way we use the tools set for the project specific versions over your computer. 
You can do so by adding the following to the end of the same profile mentioned above:

```bash
export PATH=./bin:$PATH
```


Installing The Project for Development
--------------------------------------

Clone the repository:

```bash
git clone git@github.com:juanmrad/rapidpromoitems.com.git
cd rapidpromoitems.com
```
Start the Docker Containers:

```bash
containers up
```

Note : The ./bin/containers executable is just a wrapper for docker-compose, and saves you from having to specify the
project name and docker-compose.yml paths each time you want to work with the containers.

Update your hosts file:

```bash
echo -e "\n\
127.0.0.1 jikan-rest.local www.jikan-rest.local\n\
" | sudo tee -a /etc/hosts
```

Initialize the project:

```bash
init_project
```

### Docker Containers : Creating, starting, stopping and deleting

docker-compose (and therefore the ./bin/containers wrapper) can be used to manage the containers. Type docker-compose
for a list of available commands. Commonly, you will want to use the following:

```bash
containers up # creates and starts the containers for the project.
containers down # stops and deletes the containers for the project.
```


