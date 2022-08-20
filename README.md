# Symfony in microservice architecture

The goal of this project is to try to give experience of using Symfony in Microservice Architecture.

Actually this project make a synchronous communication between a Symfony Project (Inventory) and a Golang project (Finance) through gRPC.

## Requirements

1. Docker Compose.
2. [Taskfile](https://taskfile.dev/installation/).
3. A gRPC client [gRPCurl]([https://github.com/fullstorydev/grpcurl) or [Insomnia](https://insomnia.rest/download) (optional).
# Taskfile

## Run and setup all microservices
Use this command from root directory, to run and setup the both microservices.

```console
$ task fire-all
```

## Run microservice individually

To run Inventory (Symfony project) and init database, run this command inside `inventory` directory or inside `finance` directory to run Finance (Golang project).

```console
$ task fire-all
```

# Watch mode

Due to [Roadrunner](https://roadrunner.dev/) config file `.rr.dev.yaml` and [Air](https://github.com/cosmtrek/air) config file `.air.toml` , both project (microservice) run in watch mode, meaning whenever you make a change into the `inventory` or `finance` directories, those changes are rebuild into containers instantly.

- - -

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/yellow_img.png)](https://www.buymeacoffee.com/achrefriahi)