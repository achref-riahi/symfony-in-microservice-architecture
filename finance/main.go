package main

import (
	"achrefriahi/finance/protobuf/finance"
	"net"

	log "github.com/sirupsen/logrus"

	"google.golang.org/grpc"
)

func main() {
	listen, err := net.Listen("tcp", ":9000")
	if err != nil {
		log.WithError(err).Error("Failed to listen.")
	}
	log.Info("Start listing gRPC service on port 9000.")
	grpcServer := grpc.NewServer()
	s := finance.Server{}
	finance.RegisterFinanceServer(grpcServer, &s)
	if err := grpcServer.Serve(listen); err != nil {
		log.WithError(err).Error("Failed to serve.")
	}
}
