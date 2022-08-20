package finance

import (
	"context"
	"encoding/json"
	"io/ioutil"
	"net/http"
	"time"

	log "github.com/sirupsen/logrus"
	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/status"
)

type Server struct {
	UnimplementedFinanceServer
}

type ExchangeRateConvertResponse struct {
	Motd struct {
		Msg string `json:"msg"`
		URL string `json:"url"`
	} `json:"motd"`
	Success bool `json:"success"`
	Query   struct {
		From   string  `json:"from"`
		To     string  `json:"to"`
		Amount float64 `json:"amount"`
	} `json:"query"`
	Info struct {
		Rate float64 `json:"rate"`
	} `json:"info"`
	Historical bool    `json:"historical"`
	Date       string  `json:"date"`
	Result     float64 `json:"result"`
}

func (s *Server) GetExchangeRate(ctx context.Context, in *GetExchangeRateRequest) (*GetExchangeRateResponse, error) {
	log.WithFields(log.Fields{"from": in.From.String(), "to": in.To.String()}).Info("Call GetExchangeRate gRPC service")
	if !isCurrencyArgumentValid(in.From) || !isCurrencyArgumentValid(in.To) {
		errMessage := "Bad or missing argument."
		log.Error(errMessage)
		err := status.Error(codes.InvalidArgument, errMessage)
		return nil, err
	}

	exchangeRateConvert, err := getExchangeRateConvert(in.From.String(), in.To.String())
	if err != nil {
		errMessage := "Error occurred when calling exchangerate.host API."
		log.Error(errMessage)
		err := status.Error(codes.FailedPrecondition, errMessage)
		return nil, err
	}
	return &GetExchangeRateResponse{Rate: exchangeRateConvert.Info.Rate}, nil
}

func isCurrencyArgumentValid(val Currency) bool {
	return val != Currency_UNKNOWN
}

func getExchangeRateConvert(from, to string) (*ExchangeRateConvertResponse, error) {
	client := http.Client{
		Timeout: 2 * time.Second,
	}
	request, err := http.NewRequest("GET", "https://api.exchangerate.host/convert?from="+from+"&to="+to, nil)
	if err != nil {
		log.WithError(err).Error("Failed to generate request.")
		return nil, err
	}
	response, err := client.Do(request)
	if err != nil {
		log.WithError(err).Error("Failed to request.")
		return nil, err
	}
	body, err := ioutil.ReadAll(response.Body) // response body is []byte
	if err != nil {
		log.WithError(err).Error("Failed to serialize response.")
		return nil, err
	}
	var result ExchangeRateConvertResponse
	if err := json.Unmarshal(body, &result); err != nil { // Parse []byte to the go struct pointer
		log.Error("Can not unmarshal JSON")
		return nil, err
	}
	return &result, nil
}
